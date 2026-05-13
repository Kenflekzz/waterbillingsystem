<#
PowerShell helper to generate USER_GUIDE.pdf from USER_GUIDE.md.
Requirements (one of):
- `pandoc` (recommended) with a PDF engine (LaTeX) OR
- `wkhtmltopdf` (for HTML -> PDF conversion)

Usage:
  .\docs\generate_user_guide_pdf.ps1

The script will try `pandoc` first, then `wkhtmltopdf`. If neither is found it will print instructions.
#>

$md = "docs/USER_GUIDE.md"
$pdf = "docs/USER_GUIDE.pdf"

function Has-Command($name) { return (Get-Command $name -ErrorAction SilentlyContinue) -ne $null }

if (Has-Command pandoc) {
    Write-Host "pandoc found — generating PDF..."
    # pandoc may require a LaTeX engine; this tries a simple route
    pandoc $md -o $pdf --from markdown --pdf-engine=xelatex
    if (Test-Path $pdf) { Write-Host "Generated $pdf" } else { Write-Host "pandoc ran but PDF not produced. Ensure a LaTeX engine is installed." }
    exit
}

if (Has-Command wkhtmltopdf) {
    Write-Host "wkhtmltopdf found — generating PDF via HTML conversion..."
    $tmpHtml = "docs/USER_GUIDE.html"
    pandoc --version > $null 2>&1
    # Try using pandoc to make HTML if available, else use the markdown file as-is (wkhtmltopdf can accept HTML only)
    if (Has-Command pandoc) {
        pandoc $md -o $tmpHtml --from markdown
    } else {
        # fallback: create a very basic HTML wrapper
        $content = Get-Content $md -Raw
        $html = "<html><body><pre>" + [System.Web.HttpUtility]::HtmlEncode($content) + "</pre></body></html>"
        $html | Out-File -FilePath $tmpHtml -Encoding UTF8
    }
    wkhtmltopdf $tmpHtml $pdf
    if (Test-Path $pdf) { Write-Host "Generated $pdf" } else { Write-Host "wkhtmltopdf ran but PDF not produced." }
    exit
}

Write-Host "Neither pandoc nor wkhtmltopdf were found on PATH."
Write-Host "Install pandoc (https://pandoc.org/) and a LaTeX engine (e.g. TinyTeX), or wkhtmltopdf (https://wkhtmltopdf.org/), then run this script again."
