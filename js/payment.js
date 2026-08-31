// ===========================================
// Smart Rental Manager
// Payment JavaScript (Member 5 — real backend wiring)
// ===========================================

const API_BASE = "../backend";

const payBtn = document.getElementById("payBtn");
const paymentAmount = document.getElementById("paymentAmount");
const paymentStatus = document.getElementById("paymentStatus");
const methodRadios = document.querySelectorAll('input[name="method"]');

let activeAgreement = null;

// ===============================
// Load the current tenant's active agreement on page load
// so we know which agreement_id to attach the payment to.
// ===============================
async function loadActiveAgreement() {
    try {
        const res = await fetch(`${API_BASE}/agreements/list.php`, {
            method: "GET",
            credentials: "include",
        });
        const data = await res.json();

        if (!data.success) {
            paymentStatus.innerHTML = data.message || "Could not load your agreement.";
            return;
        }

        const active = (data.data || []).find(a => a.status === "active");
        if (!active) {
            paymentStatus.innerHTML = "No active rental agreement found for your account.";
            payBtn.disabled = true;
            return;
        }

        activeAgreement = active;
        paymentAmount.value = active.rent_amount;
        paymentAmount.placeholder = active.rent_amount;
    } catch (err) {
        console.error(err);
        paymentStatus.innerHTML = "Could not connect to the server. Please make sure you're logged in.";
    }
}

function getSelectedMethod() {
    const checked = Array.from(methodRadios).find(r => r.checked);
    if (!checked) return null;
    return checked.nextElementSibling?.textContent.trim() || "Online Payment";
}

function generateTransactionReference() {
    return "TXN-" + Date.now() + "-" + Math.floor(Math.random() * 10000);
}

// ===============================
// Confirm Payment
// ===============================
payBtn.addEventListener("click", async () => {

    const amount = paymentAmount.value;

    if (amount === "" || Number(amount) <= 0) {
        alert("Please enter payment amount.");
        return;
    }

    if (!activeAgreement) {
        alert("No active agreement to pay against. Please try again once your agreement is confirmed.");
        return;
    }

    const method = getSelectedMethod();
    if (!method) {
        alert("Please select a payment method.");
        return;
    }

    payBtn.disabled = true;
    paymentStatus.innerHTML = "Processing payment...";

    const txnReference = generateTransactionReference();

    try {
        // 1. Create the payment record (starts as "pending")
        const createRes = await fetch(`${API_BASE}/payments/create.php`, {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                agreement_id: activeAgreement.id,
                amount: amount,
                payment_method: method,
                transaction_reference: txnReference,
            }),
        });
        const createData = await createRes.json();

        if (!createData.success) {
            paymentStatus.innerHTML = createData.message || "Payment failed.";
            payBtn.disabled = false;
            return;
        }

        const paymentId = createData.data.id;

        // 2. This is a hackathon prototype with no real payment gateway
        //    wired in yet — mark it paid immediately so the QR receipt
        //    + WhatsApp confirmation flow (backend/payments/payment-status.php)
        //    can be demoed end-to-end. Swap this for a real gateway
        //    callback when JazzCash/Easypaisa credentials are available.
        const statusRes = await fetch(`${API_BASE}/payments/payment-status.php`, {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: paymentId, status: "paid" }),
        });
        const statusData = await statusRes.json();

        if (!statusData.success) {
            paymentStatus.innerHTML = statusData.message || "Payment recorded but could not be confirmed.";
            payBtn.disabled = false;
            return;
        }

        paymentStatus.innerHTML = "Payment completed successfully!";
        alert("Payment successful. Receipt generated.");

        setTimeout(() => {
            window.location.href = `receipt.html?id=${paymentId}`;
        }, 800);

    } catch (err) {
        console.error(err);
        paymentStatus.innerHTML = "Could not connect to the server.";
        payBtn.disabled = false;
    }
});

loadActiveAgreement();
