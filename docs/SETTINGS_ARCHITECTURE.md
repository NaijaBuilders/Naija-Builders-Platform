# NaijaBuilders — Enterprise Settings Architecture

A procurement-grade settings system for a Nigerian B2B construction marketplace serving
small contractors, medium construction firms, large enterprise buyers, building-material
suppliers, and construction service providers.

Design principles:
- **Role-scoped, org-aware.** Settings live at three scopes: **User**, **Branch/Site**, **Organization**.
- **Procurement-first.** Approvals, budgets, spend controls and supplier governance are first-class, not afterthoughts.
- **Nigeria-native.** NGN, CAC/RC numbers, TIN/VAT, NDPR, state/LGA logistics, Paystack/Flutterwave, Prembly KYC.
- **Progressive disclosure.** A small contractor sees a simple page; an enterprise procurement manager sees governance tooling.

User-type tags used below: **All · Buyer · Supplier · Service** (service provider) · **Ent** (enterprise org features).

---

## TASK 1 — Business model analysis: who needs what, and why

NaijaBuilders is a **transactional + governance** marketplace. Revenue comes from take-rate on
orders, supplier subscriptions/visibility, and (later) trade-credit/financing. Settings therefore
exist to (a) reduce friction to transact, (b) enforce trust/compliance, and (c) let large orgs
control spend — the three things that drive GMV and retention in construction procurement.

### Settings every user should have (All)
Identity & contact, password/2FA/biometric, sessions & devices, notification channels, language/
currency/units, privacy/data export, support/disputes. **Why:** these are table-stakes for trust
and account safety regardless of role; without them no one transacts confidently.

### Buyer-only
Favorite/preferred suppliers, preferred brands, project/cost-center tagging, default delivery
addresses & windows, auto-reorder rules, budget management, saved payment methods, RFQ defaults.
**Why:** buyers' repeat-purchase behavior (reorders, projects) is the single biggest driver of
GMV. Reducing the clicks-to-reorder and standardizing project delivery directly increases order
volume.

### Supplier-only
Product visibility, MOQ, lead times, inventory thresholds, delivery coverage & fees, quote
automation, business hours/cut-offs, payout account, bulk-discount tiers, holiday calendar.
**Why:** suppliers win by responding fast and being discoverable. Quote automation + accurate
lead/coverage data raise quote-acceptance rates and reduce failed orders, which keeps suppliers
engaged and paying for visibility.

### Service-provider-only
Service areas, services & pricing model, availability calendar, booking lead time, team dispatch,
emergency/after-hours, call-out fees, instant-vs-request booking, max concurrent jobs.
**Why:** services are scheduling-and-coverage businesses. The settings that matter are the ones
that prevent double-booking and surface availability — that's what converts a request into a paid job.

### Enterprise (large buyers/firms) needs
Multi-branch/site structure, team RBAC (procurement/finance/site managers, purchasing officers),
approval workflows & chains, spend authority per role, budgets per project/branch, PO-required
gating, category restrictions, preferred/blocked supplier governance, three-way match, audit logs,
NDPR retention, mandatory 2FA, delegated approvers, consolidated billing.
**Why:** enterprises won't move spend onto a platform they can't govern. Approval chains + budget
controls + audit are the *reason* a ₦500M/yr buyer consolidates procurement here instead of WhatsApp
and bank transfers. These features convert the largest accounts and lock in retention.

---

## TASK 2 — Settings hierarchy (navigation structure)

```
Settings
├── Account
│   ├── Profile (name, photo, username, contact)
│   ├── Company Profile (legal name, type, logo, bio, storefront)
│   ├── Business Verification (KYC / CAC / documents)
│   ├── Tax Information (TIN, VAT)
│   └── Branches & Locations (sites, default branch)
├── Security
│   ├── Password
│   ├── Biometric & PIN
│   ├── Two-Factor Authentication
│   ├── Sessions & Devices
│   ├── Login History
│   └── Security Alerts
├── Notifications
│   ├── Procurement (RFQ, quotes)
│   ├── Orders & Delivery
│   ├── Billing & Payments
│   ├── Messages
│   ├── Marketing
│   └── Channels & Quiet Hours
├── Payments & Billing
│   ├── Payment Methods
│   ├── Payout Account (supplier)
│   ├── Credit & Terms
│   ├── Invoicing
│   ├── Spending Controls (ent)
│   └── Reminders
├── Procurement (Buyer / Ent)
│   ├── RFQ Defaults
│   ├── Approval Workflows
│   ├── Budgets & Cost Centers
│   ├── Supplier Governance (preferred / blocked)
│   ├── Category Restrictions
│   └── Auto-Reorder
├── Selling (Supplier)
│   ├── Catalog Visibility
│   ├── Pricing & Quotes
│   ├── MOQ & Lead Times
│   ├── Inventory
│   ├── Delivery Coverage
│   └── Business Hours
├── Services (Service Provider)
│   ├── Service Areas
│   ├── Offerings & Pricing
│   ├── Availability & Booking
│   ├── Team & Dispatch
│   └── Emergency Services
├── Logistics
│   ├── Delivery Addresses
│   ├── Delivery Windows & Instructions
│   ├── Pickup & Fleet
│   ├── Warehouses
│   └── Proof of Delivery
├── Team & Permissions (Ent)
│   ├── Members
│   ├── Roles & Permissions
│   ├── Approval Chains
│   ├── Branch Access
│   └── Spend Authority
├── Marketplace Preferences
│   ├── Language & Region
│   ├── Currency & Units
│   └── Discovery Preferences
├── Data & Privacy
│   ├── Exports (data / orders / invoices)
│   ├── Consent & Personalization
│   ├── Compliance (NDPR / retention)
│   └── Delete Account
└── Support & Help
    ├── Support Tickets / Live Chat
    ├── Disputes & Reporting
    ├── Feature Requests
    └── Training & Knowledge Base
```

---

## TASK 3 & 4 — Settings catalog (150+ settings)

Columns: **ID · Name · Description · User type · Business value · Priority**.

### Account
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S001 | Display name | Personal display name | All | Identity/trust | Critical |
| S002 | Profile photo / logo | Avatar or company logo | All | Brand trust | High |
| S003 | Username/handle | Unique @handle for login & profile | All | Login UX | Medium |
| S004 | Primary email | Verified primary email | All | Account recovery | Critical |
| S005 | Phone number(s) | Verified phone(s), primary flag | All | OTP, delivery contact | Critical |
| S006 | Legal/business name | Registered company name | All(biz) | Invoicing/compliance | Critical |
| S007 | Business type | Sole trader / Ltd / Enterprise | All | Risk & feature gating | High |
| S008 | RC/CAC number | Corporate registration number | Supplier/Service/Ent | KYC, payouts | Critical |
| S009 | Verification status & resubmit | View KYC state, re-upload docs (Prembly) | All | Unlocks selling/credit | Critical |
| S010 | TIN | Tax Identification Number | Supplier/Ent | Tax compliance | High |
| S011 | VAT number & rate | VAT registration + rate applied | Supplier | Correct invoicing | High |
| S012 | Industry / trade | Primary category/trade | All | Discovery/matching | Medium |
| S013 | Year established | Company age | Supplier/Service | Buyer trust signal | Low |
| S014 | Company size band | Employee/turnover band | Ent/Supplier | Segmentation | Low |
| S015 | Branches / sites | Multi-site registry (state/LGA) | Ent/Buyer | Multi-site procurement | High |
| S016 | Default branch | Default org branch context | Ent | Reduces mis-posting | High |
| S017 | Registered address | HQ/registered address | All | Invoicing/logistics | High |
| S018 | Company bio / about | Public storefront description | Supplier/Service | Conversion | Low |
| S019 | Storefront slug/URL | Public shop link | Supplier | Marketing | Medium |
| S020 | Contact persons | Named contacts + roles | Ent | Coordination | Medium |

### Security
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S021 | Change password | Update password | All | Account safety | Critical |
| S022 | Password policy enforcement | Min length/rotation for org members | Ent | Compliance | High |
| S023 | Biometric login | Face/Touch ID unlock | All | Friction-free login | High |
| S024 | 2FA — SMS OTP | OTP to phone | All | Account safety | Critical |
| S025 | 2FA — Authenticator (TOTP) | App-based 2FA | All | Stronger MFA | High |
| S026 | 2FA — Email OTP | Email fallback OTP | All | Recovery | Medium |
| S027 | Mandatory 2FA for team | Org-enforced MFA | Ent | Risk reduction | High |
| S028 | Active sessions | List + revoke sessions | All | Breach response | High |
| S029 | Trusted devices | Manage remembered devices | All | Convenience/safety | Medium |
| S030 | Login history | Audit of logins (device/location) | All | Fraud detection | Medium |
| S031 | Security alerts | Alert on new device/location | All | Fraud detection | High |
| S032 | Payout PIN | PIN to authorize withdrawals | Supplier/Service | Funds safety | Critical |
| S033 | Transaction PIN | PIN to authorize purchases/approvals | Buyer/Ent | Spend safety | High |
| S034 | Session timeout | Auto sign-out window | Ent | Shared-device safety | Medium |
| S035 | IP allowlist | Restrict org access by IP | Ent | Enterprise security | Low |

### Notifications (each event supports channel matrix: Push · Email · SMS · In-app)
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S036 | RFQ received | New RFQ matching your catalog | Supplier | Faster quoting → GMV | Critical |
| S037 | RFQ status | Your RFQ viewed/quoted/closed | Buyer | Engagement | High |
| S038 | New quote received | Supplier quoted your RFQ | Buyer | Conversion | Critical |
| S039 | Quote accepted/declined | Outcome of your quote | Supplier | Supplier engagement | High |
| S040 | Quote expiring | Quote about to expire | Both | Recovers lost deals | Medium |
| S041 | Order placed/confirmed | Order created/accepted | All | Trust | Critical |
| S042 | Order status change | Processing/packed/etc. | All | Reduces support load | High |
| S043 | Delivery dispatched | Goods left supplier | Buyer | Visibility | High |
| S044 | Out for delivery / ETA | Live ETA window | Buyer | CX, fewer failed deliveries | High |
| S045 | Delivery completed / confirm | Confirm handover (OTP) | All | Escrow release trigger | High |
| S046 | Invoice issued | New invoice available | All | Cashflow | High |
| S047 | Payment received | Funds credited | Supplier | Supplier trust | Critical |
| S048 | Payment due / reminder | Upcoming/overdue payment | Buyer/Ent | Reduces defaults | High |
| S049 | Escrow released | Funds released to supplier | Both | Trust | High |
| S050 | New message | Chat message | All | Response time | High |
| S051 | Price drop (saved item) | Saved product cheaper | Buyer | Re-engagement | Medium |
| S052 | Back in stock | Saved/out-of-stock item available | Buyer | Order recovery | Medium |
| S053 | Approval pending | You must approve a request | Ent | Unblocks spend | Critical |
| S054 | Approval decision | Your request approved/rejected | Ent | Throughput | High |
| S055 | Low stock alert | Inventory below threshold | Supplier | Avoids oversell | High |
| S056 | Reorder due | Auto-reorder threshold reached | Buyer/Ent | Recurring GMV | High |
| S057 | Marketing / promos | Offers & campaigns | All | Upsell | Low |
| S058 | Recommendations | Personalized product picks | Buyer | Discovery | Low |
| S059 | Quiet hours / DND | Mute window per channel | All | Retention (less spam) | Medium |
| S060 | Digest mode | Daily/weekly roll-up | All | Reduces fatigue | Low |

### Payments & Billing
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S061 | Saved cards | Tokenized cards (Paystack/Flutterwave) | Buyer/Ent | Checkout speed | High |
| S062 | Bank transfer / virtual account | Dedicated NUBAN per account | All | Preferred NG payment | High |
| S063 | Payout account | Settlement bank account | Supplier/Service | Get paid | Critical |
| S064 | Default payment method | Preselected method | Buyer | Checkout speed | High |
| S065 | Trade credit / credit limit | BNPL / Net terms limit | Ent/Buyer | Bigger baskets | High |
| S066 | Payment terms | Net 7/14/30 per buyer | Ent/Supplier | B2B norm | High |
| S067 | Auto-pay invoices | Auto-settle on due date | Ent | Fewer defaults | Medium |
| S068 | Invoice branding/format | Logo, footer, numbering | Supplier/Ent | Professionalism | Medium |
| S069 | Billing recipients | Where invoices/statements go | Ent | Finance ops | Medium |
| S070 | PO required | Block payment without PO | Ent | Spend control | High |
| S071 | Spend limit per role | Max a role can spend | Ent | Governance | Critical |
| S072 | Per-transaction limit | Cap per order | Ent | Fraud/error control | High |
| S073 | Payment reminder schedule | Cadence of reminders | Buyer/Ent | Cashflow | Medium |
| S074 | Settlement currency | Supplier payout currency | Supplier | Clarity | Medium |
| S075 | Auto-payout schedule | When balance is swept | Supplier | Cashflow | Medium |
| S076 | Refund/dispute preference | Default refund handling | All | Trust | Medium |

### Procurement (Buyer / Enterprise)
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S077 | Default RFQ template | Reusable RFQ presets | Buyer/Ent | Faster sourcing | High |
| S078 | RFQ validity period | Default quote deadline | Buyer | Process speed | Medium |
| S079 | Min quotes before award | Require N quotes | Ent | Compliance | Medium |
| S080 | Approval workflow builder | Multi-step approval design | Ent | Governance | Critical |
| S081 | Approval threshold tiers | ₦ tiers → approver level | Ent | Spend control | Critical |
| S082 | Preferred supplier list | Whitelist suppliers | Buyer/Ent | Quality/price | High |
| S083 | Blocked suppliers | Blacklist | Ent | Risk | Medium |
| S084 | Category restrictions | Limit who buys what | Ent | Policy | High |
| S085 | Budget per project/branch | Spend ceilings | Ent | Cost control | Critical |
| S086 | Budget alerts | Notify at % thresholds | Ent | Overrun prevention | High |
| S087 | Three-way match | PO=GRN=Invoice gate | Ent | Audit | Medium |
| S088 | Procurement policy doc | Attach policy PDF | Ent | Governance | Low |
| S089 | Auto-RFQ to preferred | Broadcast to whitelist | Ent | Speed | Medium |

### Buyer-specific
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S090 | Favorite suppliers | Quick-access suppliers | Buyer | Repeat orders | Medium |
| S091 | Preferred brands | Brand preferences in search | Buyer | Match quality | Medium |
| S092 | Project delivery defaults | Per-project address/window | Buyer | Reorder speed | High |
| S093 | Default delivery window | Preferred time slot | Buyer | CX | Medium |
| S094 | Auto-reorder rules | Material + threshold + cadence | Buyer/Ent | Recurring GMV | High |
| S095 | Reorder cadence | Weekly/monthly schedules | Buyer | Recurring GMV | Medium |
| S096 | Project / cost-center tags | Tag spend to projects | Ent | Cost accounting | High |
| S097 | Default cost center | Auto-tag new orders | Ent | Finance ops | Medium |

### Supplier-specific
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S098 | Catalog visibility | Public / private / per-buyer | Supplier | Channel control | High |
| S099 | MOQ | Min order qty per product/category | Supplier | Margin protection | High |
| S100 | Lead times | Per-product fulfillment time | Supplier | Accurate ETAs | High |
| S101 | Inventory thresholds | Low-stock + auto-hide at 0 | Supplier | Prevents oversell | High |
| S102 | Delivery coverage | States/LGAs served | Supplier | Correct matching | High |
| S103 | Delivery fee rules | Distance/weight pricing | Supplier | Margin | Medium |
| S104 | Quote automation | Instant pricing rules | Supplier | Win rate | High |
| S105 | Business hours / cut-off | Order cut-off + open hours | Supplier | Expectation setting | Medium |
| S106 | Holiday calendar | Closure dates | Supplier | Fewer failed orders | Low |
| S107 | Negotiation toggle | Allow price negotiation | Supplier | Conversion | Medium |
| S108 | Bulk-discount tiers | Qty-break pricing | Supplier | Bigger baskets | High |

### Service Provider
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S109 | Service areas | Coverage (states/LGAs/radius) | Service | Correct matching | High |
| S110 | Offerings & pricing model | Fixed/hourly/quote | Service | Conversion | High |
| S111 | Availability calendar | Working days/hours | Service | Prevents double-book | High |
| S112 | Booking lead time/buffer | Min notice + gap between jobs | Service | Ops feasibility | Medium |
| S113 | Team & dispatch | Assign jobs to crew | Service | Scale | Medium |
| S114 | Emergency/after-hours | Offer + surcharge | Service | Premium revenue | Medium |
| S115 | Call-out fee | Base visit fee | Service | Margin | Medium |
| S116 | Instant vs request booking | Auto-confirm or review | Service | Conversion vs control | Medium |
| S117 | Max concurrent jobs | Capacity cap | Service | Quality | Low |

### Logistics
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S118 | Delivery address book | Saved sites/addresses | Buyer/Ent | Reorder speed | High |
| S119 | Delivery windows | Allowed time windows per site | Buyer/Ent | Site coordination | High |
| S120 | Delivery instructions | Access notes, contact on site | Buyer | Fewer failed deliveries | Medium |
| S121 | Default pickup location | Supplier dispatch origin | Supplier | Logistics accuracy | Medium |
| S122 | Fleet / vehicles | Vehicle registry + capacity | Supplier | Self-delivery | Medium |
| S123 | Driver accounts | Driver app access | Supplier | Tracking | Low |
| S124 | Tracking/ETA sharing | Share live tracking link | All | CX | Medium |
| S125 | Warehouses | Multi-location stock | Supplier/Ent | Fulfillment | Medium |
| S126 | Proof of delivery | Photo + OTP requirement | All | Dispute prevention | High |

### Team & Permissions (Enterprise)
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S127 | Invite member | Add team member by email/phone | Ent/Supplier | Team scale | Critical |
| S128 | Role assignment (RBAC) | Assign predefined/custom roles | Ent | Governance | Critical |
| S129 | Procurement Manager role | Approve POs, manage suppliers | Ent | Governance | High |
| S130 | Finance Manager role | Payments, credit, invoices | Ent | Governance | High |
| S131 | Site Manager role | Branch-scoped requesting/receiving | Ent | Field ops | High |
| S132 | Purchasing Officer role | Create RFQ/PO, no approve | Ent | Separation of duties | High |
| S133 | Approval chain assignment | Map roles to approval steps | Ent | Control | Critical |
| S134 | Branch-scoped access | Limit user to branches | Ent | Least privilege | High |
| S135 | Spend authority per role | ₦ authority by role/branch | Ent | Control | Critical |
| S136 | Deactivate/offboard | Revoke access instantly | Ent | Security | High |
| S137 | Delegate approver | Cover for out-of-office | Ent | Throughput | Medium |

### Marketplace Preferences
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S138 | Language | EN / Hausa / Yoruba / Igbo | All | Accessibility | Medium |
| S139 | Currency display | NGN default, others view-only | All | Clarity | Medium |
| S140 | Units of measurement | Bag/ton/truckload/metric | All | Accuracy | High |
| S141 | Region/state defaults | Default location context | All | Relevance | Medium |
| S142 | Product interests | Followed categories | Buyer | Discovery | Low |
| S143 | Discovery preferences | Verified-only, local-first, sort | Buyer | Quality | Medium |

### Data & Privacy
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S144 | Export account data | NDPR data export | All | Compliance/trust | Medium |
| S145 | Export purchase history | CSV/PDF order history | Buyer/Ent | Accounting | High |
| S146 | Export invoices/statements | Bulk invoice export | All | Accounting | High |
| S147 | Marketing consent | Granular consent toggles | All | NDPR | Medium |
| S148 | Personalization controls | Limit data-driven personalization | All | Trust | Low |
| S149 | Audit log access | Org activity log export | Ent | Compliance | Medium |
| S150 | Data retention policy | Retention window settings | Ent | NDPR | Medium |
| S151 | Delete account/data | Self-service deletion | All | NDPR right | Medium |

### Support & Help
| ID | Name | Description | User | Business value | Priority |
|----|------|-------------|------|----------------|----------|
| S152 | Support tickets | Create/track tickets | All | Retention | Medium |
| S153 | Live chat | Real-time support | All | Retention | Medium |
| S154 | Report a supplier | Flag bad supplier | Buyer | Marketplace quality | Medium |
| S155 | Report a buyer | Flag bad buyer | Supplier | Marketplace quality | Medium |
| S156 | Dispute center | Open/track order disputes | All | Trust | High |
| S157 | Feature requests | Submit ideas | All | Roadmap | Low |
| S158 | Training / knowledge base | Guides & videos | All | Adoption | Low |
| S159 | Dedicated account manager | Enterprise concierge | Ent | Retention | Medium |

> **Total: 159 settings.**

---

## TASK 5 — Role dashboards (what each role sees)

### Buyer (small contractor / individual)
`Account · Security · Notifications · Payments & Billing · Procurement (RFQ defaults, favorites,
auto-reorder) · Logistics (addresses, windows) · Marketplace Preferences · Data & Privacy · Support`
Hidden: Selling, Services, Team & Permissions, Approval workflows, Spend controls.

### Supplier
`Account (storefront, KYC, payout) · Security · Notifications (RFQ/quotes/payments) · Payments
(payout, terms, invoicing) · Selling (catalog, pricing, MOQ, lead times, inventory, coverage,
hours) · Logistics (pickup, fleet, warehouses, POD) · Marketplace Preferences · Data & Privacy ·
Support`
Hidden: Procurement, Services, buyer budgets.

### Service Provider
`Account (KYC, license/trade docs) · Security · Notifications (bookings/jobs/payments) · Payments
(payout) · Services (areas, offerings, availability, team dispatch, emergency, pricing) ·
Logistics (service addresses) · Marketplace Preferences · Data & Privacy · Support`
Hidden: Selling catalog/inventory, Procurement.

### Enterprise Procurement Manager
Everything a Buyer sees **plus** the governance layer:
`Company Profile (branches) · Team & Permissions (roles, approval chains, spend authority,
branch access) · Procurement (approval workflows, budgets, category restrictions, preferred/
blocked suppliers, three-way match) · Payments (credit/terms, PO-required, spend limits,
consolidated billing) · Data & Privacy (audit logs, retention) · Dedicated account manager.`
Scope switcher at top: **Organization ▸ Branch ▸ Personal**.

---

## TASK 6 — Mobile UI design (iOS / Android / Flutter)

### Global pattern
- **Entry:** gear icon top-right of Profile → **Settings home** = a grouped, searchable list with section icons, a role/scope switcher chip at top, and a search bar.
- **Navigation flow:** `Settings home → Section list → Detail screen → (sub-detail / editor sheet)`. Master-detail on tablets; stacked push on phones.
- **Scope switcher:** segmented control `Personal | Branch | Organization` (enterprise only) that re-scopes every screen below.

### Control vocabulary
- **Toggle (Switch):** binary prefs (notifications, biometric, auto-reorder on/off, instant booking).
- **Segmented control:** small mutually-exclusive sets (theme, booking mode, currency).
- **Dropdown / picker sheet:** language, units, payment terms, role.
- **Stepper / numeric + currency field:** MOQ, thresholds, spend limits, budgets.
- **Multi-select chips:** delivery coverage (states/LGAs), categories, preferred suppliers.
- **List + add/edit rows:** branches, addresses, team members, payment methods, fleet.
- **Channel matrix:** each notification = row with 4 toggles (Push/Email/SMS/In-app) or an expandable row.
- **Workflow builder:** vertical stepper cards (Step 1 → approver, threshold) with drag handles.

### Per-section layout notes
- **Account/Company/Tax:** grouped form cards; verification shows a status banner (Verified / Pending / Action needed) + document upload.
- **Security:** list rows with status badges; 2FA shows method cards; Sessions = list with "revoke" swipe action.
- **Notifications:** category sections, each expandable to the channel matrix; global Quiet Hours card on top.
- **Payments:** method cards with default badge; supplier payout shows a verified-bank card; spend controls use currency steppers.
- **Procurement / Team (enterprise):** dedicated **management screens** — Approval Chain builder (vertical stepper), Budget dashboard (progress bars per project/branch), Roles matrix (role × permission grid), Member detail (role + branch access + spend authority).
- **Selling/Services:** coverage map picker + multi-select chips; availability calendar; pricing tier table editor.

### Platform specifics
- **iOS:** `UITableView` **Grouped/Inset Grouped** lists, native `UISwitch`, `UIMenu`/picker sheets, SF Symbols, swipe actions, large titles.
- **Android (Material 3):** `Preference`/`SwitchPreferenceCompat` screens or Compose `ListItem` + `Switch`, bottom sheets for pickers, Material chips, FAB for "add member/address".
- **Flutter (this stack is RN today; Flutter equiv):** `ListView` + `SwitchListTile`, `ExpansionTile` for channel matrices, `showModalBottomSheet` pickers, `ReorderableListView` for approval steps, `flutter_form_builder` for editors. Mirror with `react-native` `SectionList`, `Switch`, and bottom-sheet pickers in the current app.

---

## TASK 7 — Top 25 highest-value settings (ranked)

Ranked by combined impact on retention, order volume, procurement efficiency, supplier engagement, and revenue.

1. **Approval workflows + threshold tiers (S080/S081)** — unlocks enterprise GMV.
2. **Budgets per project/branch (S085)** — the reason enterprises consolidate spend.
3. **Auto-reorder rules (S094)** — recurring, low-friction GMV.
4. **Quote automation (S104)** — raises supplier win-rate and speed.
5. **Spend authority per role (S135)** — governance that converts large accounts.
6. **Preferred supplier list (S082)** — concentrates repeat orders.
7. **New-quote + RFQ-received alerts (S036/S038)** — response time = conversion.
8. **Payout account + payment received (S063/S047)** — supplier trust/retention.
9. **Trade credit / terms (S065/S066)** — bigger baskets, B2B norm.
10. **Delivery coverage + lead times (S102/S100)** — fewer failed orders.
11. **Branch/site structure (S015/S134)** — enterprise multi-site onboarding.
12. **Saved payment methods + virtual account (S061/S062)** — checkout conversion.
13. **Proof of delivery + confirm handover (S126/S045)** — escrow trust loop.
14. **Bulk-discount tiers (S108)** — increases average order value.
15. **PO-required + three-way match (S070/S087)** — enterprise compliance.
16. **Project/cost-center tagging (S096)** — sticky for construction accounting.
17. **Biometric + 2FA (S023/S024)** — trust + frictionless return.
18. **MOQ + inventory thresholds (S099/S101)** — prevents costly oversell.
19. **Delivery windows + instructions (S119/S120)** — site-delivery success.
20. **Notification quiet hours/digest (S059/S060)** — reduces churn from spam.
21. **Availability calendar (S111)** — service conversion without double-booking.
22. **Default RFQ template (S077)** — faster sourcing for power buyers.
23. **Favorite suppliers/brands (S090/S091)** — habit formation.
24. **Dispute center (S156)** — protects trust at scale.
25. **Export purchase history/invoices (S145/S146)** — finance stickiness.

---

## TASK 8 — 50 advanced enterprise features competitors miss

1. Branch-level purchasing controls (per-site catalogs/limits)
2. Project-specific budgets with burn-down tracking
3. Material reorder automation (consumption-based, not just threshold)
4. Supplier scorecards (on-time %, quote speed, defect rate, price index)
5. Configurable approval chains (serial + parallel + conditional)
6. Delivery time windows with site-level SLAs
7. Vendor risk management (KYC expiry, CAC status, concentration risk)
8. Three-way match (PO/GRN/Invoice) auto-reconciliation
9. Budget overrun blocking with override + audit trail
10. Multi-currency display with NGN settlement (for diaspora/importers)
11. Cost-center & GL-code tagging with accounting export (Sage/QuickBooks/Zoho)
12. Contract pricing (negotiated buyer-specific price books)
13. Punch-out style catalogs per buyer
14. Spend analytics dashboard (category, supplier, project, branch)
15. Maverick-spend detection (off-contract purchase alerts)
16. Delegated authority with time-boxed out-of-office cover
17. Tiered emergency/after-hours service pricing
18. Supplier capacity calendar (lead-time-aware availability)
19. Bulk RFQ to multiple suppliers with side-by-side comparison
20. Automated quote comparison & recommendation (price/lead/score)
21. Retention / partial payment & milestone payments for projects
22. Trade credit underwriting signals (order history, on-time payment score)
23. Group/volume buying (consortium purchasing across branches)
24. Geo-fenced delivery confirmation (GPS + photo POD)
25. Driver app with live tracking + ETA sharing
26. Warehouse/multi-location inventory allocation
27. Backorder & substitution rules (auto-suggest equivalents)
28. Price-change & price-lock notifications on contracted items
29. Reorder from past order / from BOQ (bill of quantities import)
30. BOQ-to-RFQ converter (upload BOQ → multi-line RFQ)
31. Site-based receiving with partial GRN
32. Supplier onboarding workflow with document expiry reminders
33. Sustainability/compliance attributes (certified materials)
34. Configurable invoice numbering & WHT/VAT handling
35. Withholding tax (WHT) automation on supplier payments
36. Audit log with immutable export (NDPR/ISO evidence)
37. SSO / SAML for enterprise identity (later)
38. API keys & webhooks for ERP integration
39. Role-based data masking (hide pricing from site managers)
40. Spend limit "soft cap" warnings vs "hard cap" blocks
41. Recurring orders / standing orders (scheduled procurement)
42. Supplier diversity / local-content reporting (Nigeria local content)
43. Dispute SLA timers with auto-escalation
44. Multi-step delivery (split shipments, partial fulfillment)
45. Buyer credit dashboard (available credit, utilization, due dates)
46. Procurement policy enforcement engine (rules → block/warn)
47. Quote validity auto-extension requests
48. Preferred payment terms per supplier relationship
49. Team activity feed & per-user spend reports
50. White-label storefront for large suppliers/distributors

---

## Database schema recommendations

Core principle: **polymorphic, scoped settings** + dedicated tables for relational/enforced data.

```
-- Org & structure
organizations(id, name, type[contractor|firm|enterprise|supplier|service], rc_number, tin, vat_no, ...)
branches(id, organization_id, name, state, lga, address, is_default)
memberships(id, user_id, organization_id, branch_id NULL, role_id, status)
roles(id, organization_id NULL, name, is_system)            -- system + custom roles
permissions(id, key)                                         -- e.g. 'po.approve','catalog.edit'
role_permissions(role_id, permission_id)
member_spend_authority(membership_id, branch_id, currency, max_per_txn, max_monthly)

-- Generic preferences (fast to ship, flexible)
settings(id, scope_type[user|branch|org], scope_id, namespace, key, value JSON, updated_by, updated_at)
   -- unique(scope_type, scope_id, namespace, key)
notification_preferences(id, user_id, event_key, push, email, sms, in_app, quiet_from, quiet_to)

-- Procurement governance (enforced → relational, not JSON)
approval_workflows(id, organization_id, name, applies_to[po|payment], active)
approval_steps(id, workflow_id, position, approver_role_id NULL, approver_user_id NULL, type[serial|parallel])
approval_thresholds(id, workflow_id, min_amount, max_amount, currency, step_id)
budgets(id, organization_id, branch_id NULL, project_id NULL, period, amount, spent, alert_pct[])
preferred_suppliers(id, organization_id, supplier_id, status[preferred|blocked])
category_restrictions(id, organization_id, role_id, category_id, allow)

-- Commerce config
supplier_settings(supplier_id, visibility, default_hours JSON, cutoff_time, negotiable_default)
product_overrides(product_id, moq, lead_time_days, low_stock_threshold, hide_when_zero)
delivery_coverage(supplier_id, state, lga, fee_rule JSON)
service_settings(provider_id, areas JSON, availability JSON, emergency JSON, booking_mode)

-- Payments
payment_methods(id, owner_type, owner_id, type[card|bank|virtual], token, is_default, last4, ...)
payout_accounts(id, supplier_id, bank_code, account_number_masked, verified)
credit_profiles(id, organization_id, limit, used, terms[net7|net14|net30], status)

-- Logistics
addresses(id, owner_type, owner_id, label, state, lga, instructions, default_window JSON)
fleet_vehicles(id, supplier_id, plate, type, capacity)
warehouses(id, owner_id, name, state, lga)

-- Compliance
audit_logs(id, organization_id, actor_id, action, target, before JSON, after JSON, ip, created_at)
```

Guidance:
- **JSON `settings` table** for simple toggles/prefs (notifications channels, units, language).
- **Relational tables** for anything **enforced or queried** (budgets, approvals, spend authority, credit) — never bury governance in JSON blobs.
- Index by `(scope_type, scope_id)`; cache resolved settings per request with an inheritance merge: **org → branch → user** (most specific wins).

## API recommendations

```
GET  /api/settings?scope=user|branch:{id}|org           -> resolved (merged) settings
PATCH/api/settings                                        -> { scope, namespace, changes:{...} }
GET  /api/settings/notifications  · PATCH (channel matrix)
GET  /api/org/{id}/branches  · POST · PATCH · DELETE
GET  /api/org/{id}/members   · POST(invite) · PATCH(role/branch/authority) · DELETE
GET  /api/org/{id}/roles     · POST · PATCH (permissions)
GET  /api/org/{id}/approval-workflows · POST · PATCH · /test (dry-run a ₦ amount)
GET  /api/org/{id}/budgets   · POST · PATCH · GET /budgets/{id}/usage
GET  /api/supplier/settings  · PATCH      (visibility, MOQ, lead, coverage, hours)
GET  /api/service/settings   · PATCH      (areas, availability, pricing)
GET  /api/payments/methods   · POST · DELETE · PATCH(default)
POST /api/data-exports       -> async job -> webhook/email link
```
Conventions: scope as a query param/header (`X-Org-Id`, `X-Branch-Id`); PATCH-style partial updates
(already adopted for mobile profile); every mutating call writes an `audit_logs` row; resolved-settings
endpoint returns the merged result so clients never compute inheritance.

## Permission model recommendations

- **RBAC + scopes + spend authority.** A membership = `user × org × (branch?) × role`. Permissions are
  fine-grained keys (`po.create`, `po.approve`, `payment.execute`, `catalog.edit`, `team.manage`,
  `budget.manage`, `settings.org.edit`).
- **Scopes:** every permission resolves against a scope (org-wide vs branch-limited). Site managers
  get branch-scoped permissions only.
- **Spend authority** is a *separate dimension* from permissions: a Purchasing Officer may `po.create`
  but have ₦0 approval authority; approval routes by **amount → workflow step → approver role/user**.
- **System roles** (Owner, Procurement Manager, Finance Manager, Site Manager, Purchasing Officer,
  Viewer) ship by default; enterprises can clone to **custom roles**.
- **Separation of duties:** the requester cannot be the sole approver above their authority; enforce in
  the approval engine.
- **Delegation:** time-boxed approver delegation with full audit.

---

## Final roadmap

### Phase 1 — Must Have (foundation & transactional trust)
- Account, Company Profile, Verification (KYC), Tax
- Security: password, biometric, 2FA, sessions, login history
- Notifications with channel matrix + quiet hours (S036–S060)
- Payments: methods, virtual account, supplier payout, default method
- Supplier basics: visibility, MOQ, lead times, inventory, coverage, hours
- Buyer basics: addresses, delivery windows, favorites, RFQ defaults
- Service basics: areas, availability, offerings
- Logistics: address book, proof of delivery
- Marketplace prefs (language/currency/units), Data export, Support/disputes

### Phase 2 — Growth (efficiency & engagement)
- Auto-reorder + reorder cadence, preferred brands, project delivery defaults
- Quote automation, bulk-discount tiers, negotiation toggles
- Bank/credit basics, payment reminders, invoice branding
- Favorite/preferred suppliers, discovery preferences
- Service team dispatch, emergency services, instant booking
- Fleet/warehouses, tracking/ETA sharing
- Supplier scorecards (read-only), spend analytics (basic)

### Phase 3 — Enterprise (governance & scale)
- Branches/sites + org→branch→user scope switcher
- Team & Permissions (RBAC, custom roles, branch access)
- Approval workflows + threshold tiers + spend authority + delegation
- Budgets per project/branch with alerts & overrun blocking
- PO-required, three-way match, category restrictions, preferred/blocked governance
- Trade credit/terms, consolidated billing, WHT/VAT automation
- Audit logs + NDPR retention, ERP/accounting export, API keys/webhooks, SSO/SAML

---
*Spec authored for NaijaBuilders. Settings IDs (S001–S159) are stable references for backend/mobile implementation tickets.*
