# CustomFieldsReadOnly — FreeScout Module

Extends FreeScout's [Custom Fields](https://freescout.net/module/custom-fields/) module to let administrators mark fields as **API-only** (read-only in the UI) or **hidden from the ticket view** entirely. This protects fields that are managed programmatically via the API from being accidentally edited through the web interface.

## Features

- **API Only** — field is visible in the conversation view but all inputs are disabled. A grey "API Only" badge is shown next to the field label so agents know why it can't be edited.
- **Hide from Ticket View** — field is invisible in the conversation view. Takes up no space. Useful for internal fields agents should never see.
- **Middleware protection** — a server-side middleware strips read-only field values from any web UI save requests, ensuring the protection can't be bypassed by disabling JavaScript.
- **Self-installing** — columns are added to the `custom_fields` table automatically on first boot; no manual migration step needed.

## Requirements

- FreeScout **1.8.181** or later
- The official **Custom Fields** module must be installed and active

## Installation

1. Download or clone this repository into your FreeScout modules directory:

   ```
   /path/to/freescout/Modules/CustomFieldsReadOnly/
   ```

2. In FreeScout go to **Admin → Modules** and activate **CustomFieldsReadOnly**.

3. That's it — the module will add the required database columns automatically on first load.

## Usage

### Configuring a field

1. Go to **Admin → Mailboxes → {your mailbox} → Custom Fields**.
2. Each field now has two new toggles beneath its existing settings:
   - **API Only** — enables read-only mode in the ticket view.
   - **Hide from Ticket View** — hides the field completely.
3. Toggling either switch saves immediately via AJAX — no page reload required.

### Conversation view behaviour

| Field state | What agents see |
|---|---|
| Normal | Editable as usual |
| API Only | Field shown, inputs disabled, "API Only" badge displayed |
| Hidden | Field not rendered at all |
| Both | Field not rendered at all (hidden takes priority) |

### Screenshots

**API Only — field greyed out in the conversation view**
![API Only field greyed out](https://github.com/user-attachments/assets/e8118a0c-8100-4108-bfaa-041d08d5c499)

**Admin toggle — Hide from Ticket View**
![Hide from Ticket View toggle](https://github.com/user-attachments/assets/ad94754d-298c-4fe0-9c9a-0ce5ae488773)

**Admin toggle — API Only**
![API Only toggle](https://github.com/user-attachments/assets/bbe776b6-560a-4840-85f2-787d246e062a)

### API behaviour

The read-only and hidden flags only apply to the **web UI**. API requests are unaffected — fields can be read and written normally through the FreeScout API regardless of how they are configured here.

## How it works

The module hooks into FreeScout's event system (Eventy) to:

- Inject toggle controls into the Custom Fields admin page
- Output field state as JavaScript variables on the conversation view, which the frontend uses to disable or hide the relevant inputs
- Register a middleware on the `web` group that intercepts the custom fields save endpoint and removes any values belonging to read-only fields before they reach the controller

## Author

Built by [Hamlet Digital](https://hamlet-digital.co.uk)

## License

[AGPL-3.0-or-later](LICENSE) — GNU Affero General Public License v3.0 or later.
