# PJSIP trunk compatibility

Run from the repository root:

```sh
php core/tests/pjsip-trunks.php
```

The standalone tests use fake database and Manager connections. The golden
configuration in `fixtures/pjsip-legacy.conf` was captured from the generator
before commit `f9a6d721`, using the three traditional trunks in the test.
They cover unchanged legacy output, explicit identification, destination ports,
IPv6, independent proxy ports, receive registration without a static server,
invalid input, global settings persistence and dependency validation, and custom
include precedence. They do not establish SIP interoperability with a live PBX.

## Enabling Wuzapi identification

Deploy the changes to **both core and sipsettings**, including
`core/pjsip.functions.php` and the updated `core/etc/pjsip.conf`. Apply must
generate `pjsip_global_additional.conf` and the deployed `pjsip.conf` must include
it inside `[global]`, before `pjsip_custom_post.conf`.

1. In **PJSIP Settings → Advanced General Settings → Endpoint Identifier Order**,
   explicitly enable `auth_username`. For the standard order, use
   `ip,username,auth_username,anonymous`. Retain any existing custom identifiers
   and their priority. An empty field emits no managed override.
2. Save and Apply. Check the order currently loaded by Asterisk on that page,
   or run `asterisk -rx "pjsip show settings"`. Available identifiers from
   `pjsip show identifiers` are a different list.
3. On the Wuzapi trunk select **Authentication username**, authentication
   **Inbound** (or **Both**, if needed), and set the secret. Configure Wuzapi's
   authentication username to match the trunk's endpoint name, not its display
   label. Existing endpoints and credentials are not renamed.
4. Use **Receive** when the remote party registers to Issabel. The SIP server may
   then be empty. Use a SIP server for static destinations or outgoing registration.
5. Apply and inspect `pjsip show endpoint <trunk>`. Auth-only identification
   intentionally has no generated IP identify. The combined option permits
   either method; it does not require both or override global priority.

The validator follows ordinary `#include`/`#tryinclude` directives, including
custom post overrides, without modifying custom files. Dynamic `#exec` content,
configuration templates/inheritance and realtime configuration require checking
the running order separately. The page displays both the file-derived and
running order. If the deployed configuration has no managed include, add it by
deploying the updated core configuration; changing the database alone is insufficient.

The trunk form rejects invalid ports (1–65535), arrays and multiline PJSIP input,
and requires the selected methods to be enabled. Auth identification also
requires a working Manager connection confirming the identifier is available.
Changing the global order cannot disable a previously enabled explicit method
used by a trunk or remove historical IP matching from default trunks. Existing
issues do not block unrelated settings edits when the effective order is unchanged.
An embedded legacy server port takes precedence over the separate port field.
The server port is not copied into `match` or `outbound_proxy`. An explicit
`match` with a source port is preserved.

## Live acceptance checks

On a test PBX with the target Asterisk and Wuzapi versions, verify:

- Existing IP trunks and extensions still place and receive calls.
- Wuzapi registers (if applicable), and places and receives calls with the
  expected endpoint and inbound context. An initial Digest challenge is normal.
- Incorrect credentials never reach the inbound dialplan.
- Two Wuzapi users on the same source IP reach their respective endpoints;
  check for overlapping IP identifies on other trunks if IP has priority.
- Outgoing registrations and calls use the expected destination and proxy ports.
- Saving a global order without `auth_username` is rejected while an explicit
  auth-identified trunk depends on it, and custom post overrides remain effective.

Keep Identify By at **Default (existing behavior)** on traditional trunks.
No database migration or bulk reconfiguration is required.
