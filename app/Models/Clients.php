<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;

class Clients extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'group',
        'meter_no',
        'old_meter_no', // Added: stores previous meter number
        'full_name',
        'barangay',
        'purok',
        'contact_number',
        'date_cut',
        'installation_date',
        'meter_series',
        'status',
        'user_id',
        'meter_status',
        'replacement_date',
    ];

    protected static function booted(): void
    {
        static::created(function ($client) {
            Log::info("Client created event fired", ['client_id' => $client->id, 'user_id' => $client->user_id]);
            if ($client->user_id) {
                $client->syncMeterToUser();
            }
        });

        static::updating(function ($client) {
            // Store old meter number before update
            if ($client->isDirty('meter_no')) {
                $client->old_meter_no = $client->getOriginal('meter_no');
                Log::info("Storing old meter number before update", [
                    'client_id' => $client->id,
                    'old_meter_no' => $client->old_meter_no
                ]);
            }
        });

        static::updated(function ($client) {
            Log::info("Client updated event fired", [
                'client_id' => $client->id,
                'changes' => $client->getChanges(),
                'dirty' => $client->getDirty(),
                'wasChanged_meter_no' => $client->wasChanged('meter_no'),
                'original_meter_no' => $client->getOriginal('meter_no'),
                'current_meter_no' => $client->meter_no,
                'old_meter_no_field' => $client->old_meter_no,
            ]);

            if ($client->wasChanged('meter_no')) {
                // Use the old_meter_no field we set in updating event
                $oldMeterNo = $client->old_meter_no ?? $client->getOriginal('meter_no');
                $newMeterNo = $client->meter_no;
                
                Log::info("Meter number changed detected", [
                    'client_id' => $client->id,
                    'old' => $oldMeterNo,
                    'new' => $newMeterNo
                ]);
                
                $result = $client->syncMeterToUser($oldMeterNo, $newMeterNo);
                Log::info("syncMeterToUser result", ['result' => $result]);
            }
            
            if ($client->wasChanged('user_id')) {
                Log::info("User ID changed", ['client_id' => $client->id]);
                $client->syncMeterToUser();
            }
        });
    }

    public function billings()
    {
        return $this->hasMany(Billings::class, 'client_id');
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * Sync meter number to user and notify them
     */
    public function syncMeterToUser(?string $oldMeterNo = null, ?string $newMeterNo = null): bool
    {
        Log::info("syncMeterToUser called", [
            'client_id' => $this->id,
            'user_id' => $this->user_id,
            'contact_number' => $this->contact_number,
            'meter_no' => $this->meter_no,
            'old_meter_no' => $oldMeterNo,
            'new_meter_no' => $newMeterNo
        ]);

        if (empty($this->meter_no)) {
            Log::warning("Empty meter number for client {$this->id}");
            return false;
        }

        // Get the associated user - prioritize user_id relationship
        $user = null;
        
        if ($this->user_id) {
            $user = Users::find($this->user_id);
            Log::info("Looking for user by user_id: {$this->user_id}", ['found' => $user ? 'yes' : 'no']);
        }
        
        // Fallback: find by contact number if no user_id or user not found
        if (!$user && $this->contact_number) {
            $user = Users::where('phone_number', $this->contact_number)->first();
            Log::info("Looking for user by phone: {$this->contact_number}", ['found' => $user ? 'yes' : 'no']);
            
            // Auto-link if found by phone but not linked
            if ($user && !$this->user_id) {
                Log::info("Auto-linking client {$this->id} to user {$user->id}");
                static::withoutEvents(function () use ($user) {
                    $this->update(['user_id' => $user->id]);
                });
                $this->user_id = $user->id;
            }
        }

        if (!$user) {
            Log::warning("No user found for client {$this->id}");
            return false;
        }

        Log::info("Found user", ['user_id' => $user->id, 'user_name' => $user->first_name]);

        // If we have old and new meter numbers, sync and notify
        if ($oldMeterNo && $newMeterNo && $oldMeterNo !== $newMeterNo) {
            Log::info("Calling user->syncMeterFromClient", [
                'user_id' => $user->id,
                'old' => $oldMeterNo,
                'new' => $newMeterNo
            ]);
            
            try {
                $user->syncMeterFromClient($oldMeterNo, $newMeterNo, 'System Administrator');
                Log::info("syncMeterFromClient completed successfully");
                return true;
            } catch (\Exception $e) {
                Log::error("Error in syncMeterFromClient: " . $e->getMessage());
                return false;
            }
        }

        // Simple sync without notification (for initial setup or user_id change)
        if ($user->meter_number !== $this->meter_no) {
            $user->update(['meter_number' => $this->meter_no]);
            Log::info("Updated meter number for user {$user->id} to {$this->meter_no} (no notification)");
        }

        return true;
    }
}