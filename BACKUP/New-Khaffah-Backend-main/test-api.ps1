# Script uji API Kaffah Backend
# Jalankan: .\test-api.ps1
# Pastikan server Laravel sudah jalan: php artisan serve

$base = "http://localhost:8000/api"
$results = @()

function Test-Api {
    param($Method, $Url, $Body = $null, $Token = $null)
    $headers = @{ "Accept" = "application/json" }
    if ($Token) { $headers["Authorization"] = "Bearer $Token" }
    try {
        $params = @{ Uri = $Url; Method = $Method; Headers = $headers; UseBasicParsing = $true }
        if ($Body -and $Method -eq "POST") {
            $params["Body"] = ($Body | ConvertTo-Json -Depth 5)
            $params["ContentType"] = "application/json"
        }
        $r = Invoke-WebRequest @params -TimeoutSec 10
        return @{ ok = $true; status = $r.StatusCode; note = "" }
    } catch {
        $status = $_.Exception.Response.StatusCode.value__
        $note = $_.Exception.Message
        if ($_.ErrorDetails.Message) { $note = $_.ErrorDetails.Message }
        return @{ ok = $false; status = $status; note = $note }
    }
}

Write-Host "`n=== API Public (tanpa token) ===" -ForegroundColor Cyan

# Utility (kota/kecamatan/keberangkatan butuh query param - uji dengan param)
@(
    @{ Method = "GET"; Url = "$base/utility/gelar" },
    @{ Method = "GET"; Url = "$base/utility/provinsi" },
    @{ Method = "GET"; Url = "$base/utility/kota?provinsi_id=1" },
    @{ Method = "GET"; Url = "$base/utility/kecamatan?kota_id=1" },
    @{ Method = "GET"; Url = "$base/utility/keberangkatan?paket_umrah_id=1" }
) | ForEach-Object {
    $r = Test-Api -Method $_.Method -Url $_.Url
    $name = $_.Url.Replace($base, "")
    $status = if ($r.ok) { "OK $($r.status)" } else { "FAIL $($r.status)" }
    $color = if ($r.ok) { "Green" } else { "Red" }
    Write-Host "  $name => $status" -ForegroundColor $color
    $results += [PSCustomObject]@{ Endpoint = $name; Status = $status; Note = $r.note }
}

# Paket Umrah (public)
@(
    @{ Method = "GET"; Url = "$base/paket-umrah/list-paket" },
    @{ Method = "GET"; Url = "$base/paket-umrah/paket?paket_umrah_id=1" },
    @{ Method = "GET"; Url = "$base/paket-umrah/tipe?paket_umrah_id=1" }
) | ForEach-Object {
    $r = Test-Api -Method $_.Method -Url $_.Url
    $name = $_.Url.Replace($base, "").Split("?")[0]
    $status = if ($r.ok) { "OK $($r.status)" } else { "FAIL $($r.status)" }
    $color = if ($r.ok) { "Green" } else { "Red" }
    Write-Host "  $name => $status" -ForegroundColor $color
    $results += [PSCustomObject]@{ Endpoint = $name; Status = $status; Note = $r.note }
}

# Edutrip, Haji (public list)
@(
    @{ Method = "GET"; Url = "$base/edutrip/paket" },
    @{ Method = "GET"; Url = "$base/haji/paket" }
) | ForEach-Object {
    $r = Test-Api -Method $_.Method -Url $_.Url
    $name = $_.Url.Replace($base, "")
    $status = if ($r.ok) { "OK $($r.status)" } else { "FAIL $($r.status)" }
    $color = if ($r.ok) { "Green" } else { "Red" }
    Write-Host "  $name => $status" -ForegroundColor $color
    $results += [PSCustomObject]@{ Endpoint = $name; Status = $status; Note = $r.note }
}

# Login (untuk dapat token)
Write-Host "`n=== Login (untuk API butuh auth) ===" -ForegroundColor Cyan
$loginBody = @{ email = "superadmin@example.com"; password = "password" }
try {
    $loginResp = Invoke-RestMethod -Uri "$base/login" -Method POST -Body ($loginBody | ConvertTo-Json) -ContentType "application/json" -Headers @{ "Accept" = "application/json" }
    $token = $loginResp.token
    if ($token) {
        Write-Host "  Login OK, token didapat" -ForegroundColor Green
    } else {
        Write-Host "  Login response tidak punya token (mungkin user tidak ada)" -ForegroundColor Yellow
        $token = $null
    }
} catch {
    Write-Host "  Login gagal: $($_.Exception.Message)" -ForegroundColor Yellow
    Write-Host "  Buat user superadmin atau pakai kredensial yang ada untuk uji API protected." -ForegroundColor Gray
    $token = $null
}

# API dengan auth
if ($token) {
    Write-Host "`n=== API dengan token ===" -ForegroundColor Cyan
    @(
        @{ Method = "GET"; Url = "$base/me" },
        @{ Method = "GET"; Url = "$base/paket-umrah/transaksi" },
        @{ Method = "GET"; Url = "$base/haji/transaksi" },
        @{ Method = "GET"; Url = "$base/edutrip/transaksi" }
    ) | ForEach-Object {
        $r = Test-Api -Method $_.Method -Url $_.Url -Token $token
        $name = $_.Url.Replace($base, "")
        $status = if ($r.ok) { "OK $($r.status)" } else { "FAIL $($r.status)" }
        $color = if ($r.ok) { "Green" } else { "Red" }
        Write-Host "  $name => $status" -ForegroundColor $color
        $results += [PSCustomObject]@{ Endpoint = $name; Status = $status; Note = $r.note }
    }
}

# Sistem Admin (butuh superadmin)
$adminBase = "http://localhost:8000/api/sistem-admin"
if ($token) {
    Write-Host "`n=== API Sistem Admin ===" -ForegroundColor Cyan
    @(
        @{ Method = "GET"; Url = "$adminBase/ping" },
        @{ Method = "GET"; Url = "$adminBase/dashboard/data" },
        @{ Method = "GET"; Url = "$adminBase/get-master-modul" },
        @{ Method = "GET"; Url = "$adminBase/get-master-sub-modul" },
        @{ Method = "GET"; Url = "$adminBase/paket-umrah/get-paket-umrah" },
        @{ Method = "GET"; Url = "$adminBase/paket-haji/get-paket-haji" },
        @{ Method = "GET"; Url = "$adminBase/paket-edutrip/get-paket-edutrip" }
    ) | ForEach-Object {
        $r = Test-Api -Method $_.Method -Url $_.Url -Token $token
        $name = $_.Url.Replace("http://localhost:8000/api", "")
        $status = if ($r.ok) { "OK $($r.status)" } else { "FAIL $($r.status)" }
        $color = if ($r.ok) { "Green" } else { "Red" }
        Write-Host "  $name => $status" -ForegroundColor $color
        $results += [PSCustomObject]@{ Endpoint = $name; Status = $status; Note = $r.note }
    }
}

# Ringkasan
$okCount = ($results | Where-Object { $_.Status -match "OK" }).Count
$failCount = ($results | Where-Object { $_.Status -match "FAIL" }).Count
Write-Host "`n=== Ringkasan ===" -ForegroundColor Cyan
Write-Host "  OK: $okCount | Gagal: $failCount | Total: $($results.Count)" -ForegroundColor $(if ($failCount -eq 0) { "Green" } else { "Yellow" })
if ($failCount -gt 0) {
    Write-Host "`nEndpoint yang gagal:" -ForegroundColor Red
    $results | Where-Object { $_.Status -match "FAIL" } | ForEach-Object { Write-Host "  $($_.Endpoint) - $($_.Note)" }
}
