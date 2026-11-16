# Chrome Extension → Flight Lab integration (v0.2.0)

The compatible Chrome extension distribution is `skijumping-bot-extension-v0.2.0-flightlab.zip` (provided alongside the implementation). Its seven source files belong together: `manifest.json`, `bridge-main.js`, `bridge-isolated.js`, `popup.html`, `popup.js`, `popup.css`, and `README.md`. Unpack and load the directory in `chrome://extensions` using Developer Mode → Load unpacked.

## Prerequisites
Deploy this Laravel application on a dedicated HTTPS domain; run migrations, create a Filament user, then create a Sanctum token with abilities `flight:read` and `flight:write`.

### Workflow
1. In the extension, provide your HTTPS API origin and Sanctum token; grant Chrome access to **that origin** only.
2. On Akureyri or Planica, stand on the gate and click **Pobierz najlepsze historyczne profile**. The extension fetches `GET /api/v1/strategies/best` with hill, gate and physics version.
3. Optimize normally. Saved profiles are **re-simulated** in the actual live wind as initial candidates, not replayed without validation.
4. Click **Wyślij plan i aktualny wynik skoku** after optimization to store a session, a control profile and its simulated result. Click it again **after the actual landing** to append the observed flight and a bounded telemetry sample.
5. Browse `/admin` → Flight Sessions / Strategies / Simulations / Real Jumps.

This is intentionally opt-in and **not continuous automatic uploading**. The extension avoids sending game tickets, authentication cookies or session credentials. The Laravel API token lives in `chrome.storage.local` and is used only from the extension popup context (never injected into the game page). Access to the Flight Lab domain requires an explicit Chrome permission prompt.

## Data model and safety
- `wind.phase` is the **effective phase at planned start** (`windT0 + t`), not only the initial ticket phase.
- Each jump points back to the exact session/strategy/simulation.
- `physics_version` isolates old strategies after game engine updates.
- The backend returns previously successful simulation profiles; the browser still verifies telemark and trajectory in current conditions.
- Repeated submissions from the same popup are cached by plan UUID in extension storage, but are not yet transactional across network failures. Future work should use backend idempotency keys and a persistent retry queue.
- Don't put a Sanctum token in Git, in your server HTML, or in extension source files.

## Limitations
The physical flight control algorithm remains the previously tested v0.1.5 path. Version 0.2.0 adds history seeding and manual sync, not six-phase flight control or a trained AI. The integration code has been checked for JS syntax and the Laravel endpoints have feature tests; full browser-to-VPS end-to-end testing requires a configured deployed API.

## Importing extension source to Git
Copy the seven unpacked extension files into an `extension/` directory in this repository and commit them. This backend PR does not embed the binary distribution.
