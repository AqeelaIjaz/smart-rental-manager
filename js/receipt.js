// ===========================================
// Smart Rental Manager
// Receipt JavaScript (Member 5 — real backend wiring)
// ===========================================

const API_BASE = "../backend";

const paymentDate = document.getElementById("paymentDate");
const downloadReceipt = document.getElementById("downloadReceipt");
const tenantNameEl = document.getElementById("tenantName");
const amountEl = document.getElementById("amountValue");
const statusEl = document.getElementById("statusValue");
const qrBox = document.getElementById("qrBox");
const receiptCard = document.querySelector(".receipt-container");

// ===============================
// Get payment id from the URL (?id=123), set by payment.js on redirect
// ===============================
function getPaymentIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    return id ? Number(id) : null;
}

// ===============================
// Load real receipt data
// ===============================
async function loadReceipt() {
    const paymentId = getPaymentIdFromUrl();

    if (!paymentId) {
        paymentDate.innerHTML = "No receipt selected.";
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/payments/receipt.php?id=${paymentId}`, {
            method: "GET",
            credentials: "include",
        });
        const data = await res.json();

        if (!data.success) {
            paymentDate.innerHTML = data.message || "Receipt not found.";
            return;
        }

        const receipt = data.data;

        if (tenantNameEl) tenantNameEl.innerHTML = receipt.tenant_name;
        if (amountEl) amountEl.innerHTML = `Rs. ${Number(receipt.amount).toLocaleString()}`;
        if (statusEl) {
            statusEl.innerHTML = receipt.status.charAt(0).toUpperCase() + receipt.status.slice(1);
            statusEl.className = receipt.status === "paid" ? "paid" : "";
        }

        const d = new Date(receipt.payment_date);
        paymentDate.innerHTML = isNaN(d) ? receipt.payment_date : d.toLocaleDateString();

        // Render the real QR code image if it's been generated server-side
        if (qrBox) {
            if (receipt.qr_receipt) {
                // qr_receipt is a relative path like "backend/uploads/receipts/xyz.png"
                qrBox.innerHTML = `<img src="../${receipt.qr_receipt}" alt="Payment QR Receipt" class="qr-image">`;
            } else {
                qrBox.innerHTML = `<i class="fa-solid fa-qrcode"></i><p style="font-size:12px;margin-top:8px;">QR code not yet generated for this payment.</p>`;
            }
        }

    } catch (err) {
        console.error(err);
        paymentDate.innerHTML = "Could not connect to the server.";
    }
}

// ===============================
// Download Receipt (as an image snapshot via html2canvas)
// ===============================
downloadReceipt.addEventListener("click", async () => {
    if (typeof html2canvas === "undefined") {
        // Fallback if the CDN script didn't load (e.g. offline demo)
        window.print();
        return;
    }

    try {
        const canvas = await html2canvas(receiptCard, { backgroundColor: "#ffffff", scale: 2 });
        const link = document.createElement("a");
        link.download = "rent-receipt.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
        alert("Receipt downloaded successfully!");
    } catch (err) {
        console.error(err);
        window.print();
    }
});

loadReceipt();
