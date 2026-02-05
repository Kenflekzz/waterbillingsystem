<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Users extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'meter_number',
        'phone_number',
        'email',
        'password',
        'last_login_at',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'otp_expires_at' => 'datetime'
    ];

    public function client()
    {
        return $this->hasOne(Clients::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /**
     * Create notification for meter number change
     */
    public function notifyMeterNumberChange(string $oldMeterNo, string $newMeterNo, ?string $changedBy = null): void
    {
        Log::info("Creating notification for user {$this->id}", [
            'old' => $oldMeterNo,
            'new' => $newMeterNo
        ]);

        try {
            $notification = \App\Models\Notification::create([
                'user_id' => $this->id,
                'type' => 'meter_update',
                'title' => 'Meter Number Updated',
                'message' => "Your meter number has been changed from {$oldMeterNo} to {$newMeterNo} by " . ($changedBy ?? 'System Administrator'),
                'is_read' => false,
                'read_at' => null,
            ]);

            Log::info("Notification created successfully", ['notification_id' => $notification->id]);
        } catch (\Exception $e) {
            Log::error("Failed to create notification: " . $e->getMessage());
        }
    }

    /**
     * Sync meter number from client and notify
     */
    public function syncMeterFromClient(string $oldMeterNo, string $newMeterNo, ?string $changedBy = null): void
    {
        Log::info("syncMeterFromClient called for user {$this->id}");

        // Update meter number
        $this->update(['meter_number' => $newMeterNo]);
        
        // Create notification
        $this->notifyMeterNumberChange($oldMeterNo, $newMeterNo, $changedBy);
    }

    public function reports()
    {
        return $this->hasMany(ProblemReport::class, 'client_id');
    }

    public function billings()
    {
        return $this->hasMany(UserBilling::class, 'user_id');
    }   
}