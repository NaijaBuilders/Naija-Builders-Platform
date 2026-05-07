# NaijaBuilders Mobile

Expo React Native mobile app for the NaijaBuilders B2B construction materials marketplace.

The app is a TypeScript Expo frontend connected through a service layer to the Laravel mobile API. The default development API is:

```text
https://api-dev.naijabuilders.com/api/mobile
```

## Run in Expo Go

Tunnel mode is the most reliable option when Windows Firewall or Wi-Fi isolation blocks LAN mode:

```powershell
cd mobile/app
npm run dev:mobile:tunnel
```

LAN mode is available when the phone can open `http://<pc-ip>:8081/status`:

```powershell
cd mobile/app
npm run dev:mobile
```

## Backend URL

Copy the example env file when setting up a fresh checkout:

```powershell
cd mobile/app
copy .env.example .env.local
```

The login screen also has a Backend API URL field and a Use staging API button for development testing.

## Verification

These checks should pass:

```powershell
cd mobile/app
npx tsc --noEmit
npx expo-doctor
```

## Structure

Key app folders:

```text
src/components
src/context
src/hooks
src/navigation
src/screens
src/services
src/theme
src/types
```
