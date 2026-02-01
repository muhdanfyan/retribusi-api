# Retribusi SIPANDA: System Diagrams & Logic

This document provides a visual deep dive into the operation of the SIPANDA ecosystem.

## 1. Professional Multi-Repo Architecture
The system is divided into four distinct repositories, coordinated by the **SIPANDA CORE API**.

```mermaid
graph TB
    subgraph "Core Engine (retribusi-api)"
        API["Laravel REST Engine"]
        DB[(MySQL Database)]
        Storage["Cloudinary / File Storage"]
    end

    subgraph "Admin Hub (retribusi-admin)"
        AD["Web Dashboard"]
        Stats["Revenue Analytics"]
        Verif["Verification Hub"]
    end

    subgraph "On-Ground (retribusi-petugas)"
        Field["Field App"]
        Scanner["QR/Barcode Scanner"]
        Collect["Payment Collector"]
    end

    subgraph "Citizen Portal (retribusi-mobile)"
        WP["Mobile PWA"]
        Bills["Tagihan Viewer"]
        Portal["Layanan Hub"]
    end

    API --- DB
    API --- Storage
    API <==> AD
    API <==> Field
    API <==> WP
```

---

## 2. Dynamic Registration & Verification Mechanism
One of SIPANDA's most complex features is the **Dynamic Form Registration**. The API provides a `form_schema` which the frontends render dynamically.

```mermaid
sequenceDiagram
    autonumber
    participant WP as Citizen / Petugas
    participant API as Core API
    participant ADM as OPD Admin

    WP->>API: Request Layanan Detail (ID)
    API-->>WP: Return form_schema + requirements
    WP->>WP: Render Dynamic Form
    WP->>API: Submit Data + Photos
    API->>API: Create Pending Tax Object & File URL
    API->>ADM: Alert: New Verification Required
    ADM->>API: Review Data (Approve/Reject)
    API-->>WP: Change Status -> Active
```

---

## 3. Billing & Payment Ecosystem
The system generates bills based on `rates` and `zones`.

```mermaid
flowchart TD
    Start([Period Start]) --> Master{Check Targets}
    Master -->|Individual| Single[Single Bill Generation]
    Master -->|Bulk| Group[Bulk OPD Generation]
    
    Single & Group --> Calc[Calculate Amount via Zone/Rate Class]
    Calc --> DB_Bill[(Store in Bills Table)]
    
    DB_Bill --> Notif[Push to Mobile App]
    Notif --> Wait{Wajib Pajak Action}
    
    Wait -->|View| Detail[View Invoice]
    Wait -->|Pay| PayProc[Payment Gateway / Manual]
    
    PayProc -->|Success| Receipt[Generate E-Receipt]
    Receipt --> Update[Update Table: bills.status = 'paid']
    Update --> Done([Transaction Complete])
```

---

## 4. User Persistence & Authentication (Sanctum)
Mechanism for managing session across apps.

```mermaid
graph LR
    User([User Entry]) --> Login{Login via NIK/Email}
    Login --> Verify[API: Hash Check]
    Verify --> Token[Issue personal_access_token]
    Token --> AppStorage[Stored in localStorage]
    AppStorage --> Guard[Middleware Check on Every Request]
```

---

## 5. Visual Summary
![SIPANDA Enterprise Architecture](architecture_diagram.png)
