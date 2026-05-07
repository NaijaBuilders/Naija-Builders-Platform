# Mobile Development Backend

The mobile app defaults to the cPanel staging API:

```text
https://api-dev.naijabuilders.com/api/mobile
```

Use the local Laravel server only when you specifically need to test against WAMP.

## 1. Start the Laravel API

Open a terminal:

```powershell
cd C:\wamp64\www\shop\legacy\Naijabuilders\mobile\app
npm run dev:api
```

Keep this terminal open. It serves the Laravel API at:

```text
http://127.0.0.1:8080/api/mobile
http://<your-pc-lan-ip>:8080/api/mobile
```

## 2. Start Expo

Open a second terminal:

```powershell
cd C:\wamp64\www\shop\legacy\Naijabuilders\mobile\app
npm run dev:mobile
```

Expo uses Metro port `8081`. The local Laravel helper uses API port `8080` to avoid that conflict.

If LAN mode is blocked by the Wi-Fi/router, use:

```powershell
npm run dev:mobile:tunnel
```

## 3. Test from the phone

In iPhone Safari, open:

```text
http://<your-pc-lan-ip>:8080/api/mobile/materials
```

If Safari shows JSON, Expo Go can use the backend. If Safari cannot open it, the phone is blocked from reaching the PC by Wi-Fi isolation, Windows network profile, firewall, VPN, or a different subnet.

The app login screen includes a Backend API URL field. For this local dev setup, use:

```text
http://<your-pc-lan-ip>:8080/api/mobile
```
