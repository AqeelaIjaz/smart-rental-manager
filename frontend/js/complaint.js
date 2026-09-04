// ===========================================
// Smart Rental Manager
// Voice Complaint JavaScript
// ===========================================



const recordBtn = document.getElementById("recordBtn");

const recordStatus = document.getElementById("recordStatus");

const complaintText = document.getElementById("complaintText");

const aiResponse = document.getElementById("aiResponse");

const submitComplaint = document.getElementById("submitComplaint");



let isRecording = false;

let recognition = null;

let voiceInputLang = "en"; // language to SPEAK in - separate from page's display language


const voiceLangUrBtn = document.getElementById("voiceLangUr");
const voiceLangEnBtn = document.getElementById("voiceLangEn");

function highlightSelectedVoiceLang() {
    if (!voiceLangUrBtn || !voiceLangEnBtn) return;
    voiceLangUrBtn.style.background = voiceInputLang === "ur" ? "#2d6a4f" : "#fff";
    voiceLangUrBtn.style.color = voiceInputLang === "ur" ? "#fff" : "#000";
    voiceLangEnBtn.style.background = voiceInputLang === "en" ? "#2d6a4f" : "#fff";
    voiceLangEnBtn.style.color = voiceInputLang === "en" ? "#fff" : "#000";
}

if (voiceLangUrBtn && voiceLangEnBtn) {
    voiceLangUrBtn.addEventListener("click", () => {
        voiceInputLang = "ur";
        if (recognition) recognition.lang = "ur-PK";
        highlightSelectedVoiceLang();
    });
    voiceLangEnBtn.addEventListener("click", () => {
        voiceInputLang = "en";
        if (recognition) recognition.lang = "en-US";
        highlightSelectedVoiceLang();
    });
    highlightSelectedVoiceLang();
}




// ===============================
// Real Voice Recording (Web Speech API)
// ===============================


// Browser support check - Chrome/Edge use webkitSpeechRecognition
const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognitionAPI) {

    recognition = new SpeechRecognitionAPI();
    recognition.continuous = false;
    recognition.interimResults = false;

    // Uses the Urdu/English toggle buttons on THIS page (voiceInputLang) -
    // this is independent from the app's overall display-language toggle,
    // since the person may be reading the page in one language but
    // choose to speak their complaint in another.
    function setRecognitionLanguage() {
        recognition.lang = voiceInputLang === "ur" ? "ur-PK" : "en-US";
    }

    setRecognitionLanguage();

    recognition.onresult = (event) => {
        const spokenText = event.results[0][0].transcript;
        complaintText.value = spokenText;
        analyzeComplaint(spokenText);
    };

    recognition.onerror = (event) => {
        console.error("Speech recognition error:", event.error);
        recordStatus.innerHTML = "Could not hear you clearly. Please try again or type your complaint.";
        isRecording = false;
        recordBtn.classList.remove("recording");
        recordBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
    };

    recognition.onend = () => {
        isRecording = false;
        recordBtn.classList.remove("recording");
        recordBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
        if (complaintText.value.trim() !== "") {
            recordStatus.innerHTML = "Recording completed";
        }
    };

} else {
    recordStatus.innerHTML = "Voice recording is not supported in this browser. Please type your complaint.";
    recordBtn.disabled = true;
}


recordBtn.addEventListener("click", () => {

    if (!SpeechRecognitionAPI) return;

    if (!isRecording) {

        isRecording = true;

        // re-check in case user switched the language toggle
        // after the page loaded but before pressing record
        setRecognitionLanguage();

        recordBtn.classList.add("recording");

        recordStatus.innerHTML =
        "Recording... Speak your complaint";

        recordBtn.innerHTML =
        '<i class="fa-solid fa-stop"></i>';

        recognition.start();

    } else {

        recognition.stop();
        // onend handler above resets the button state

    }

});




// ===============================
// Dispute Suggestion (rule-based, keyword matching)
// Not AI - simple if/else checks against common issue keywords
// ===============================


function analyzeComplaint(text) {

    const lowerText = text.toLowerCase();
    let suggestion = "";

    if (lowerText.includes("tap") || lowerText.includes("leak") || lowerText.includes("pipe") || lowerText.includes("water")) {
        suggestion = "This appears to be a plumbing issue. The landlord should arrange repair within a reasonable time as per the agreement.";
    } else if (lowerText.includes("electric") || lowerText.includes("wiring") || lowerText.includes("switch") || lowerText.includes("light")) {
        suggestion = "This appears to be an electrical issue. Please avoid using the affected fixture until it is repaired.";
    } else if (lowerText.includes("rent") || lowerText.includes("payment") || lowerText.includes("due")) {
        suggestion = "This appears to be a payment-related complaint. Please check your agreement terms for due dates and penalties.";
    } else if (lowerText.includes("noise") || lowerText.includes("neighbor")) {
        suggestion = "This appears to be a noise/neighbor-related complaint. The landlord will be notified to look into it.";
    } else {
        suggestion = "Your complaint has been recorded. The landlord will review it and respond as per the agreement terms.";
    }

    aiResponse.innerHTML = suggestion;

}


// Also analyze if the tenant types the complaint manually instead of recording
complaintText.addEventListener("keyup", () => {
    if (complaintText.value.trim().length > 5) {
        analyzeComplaint(complaintText.value);
    }
});




// ===============================
// Submit Complaint
// ===============================


submitComplaint.addEventListener("click", () => {

    if (complaintText.value.trim() === "") {

        alert(
            "Please record or write your complaint first."
        );

        return;

    }

    // TODO: same fix as repair.js - this needs to call the real backend
    // endpoint (e.g. backend/complaints/create.php) instead of just alerting.
    // Waiting on Kashaf/Meeral to confirm the exact endpoint + fields.

    alert(
        "Complaint submitted successfully!"
    );

    setTimeout(() => {

        window.location.href = "dashboard.html";

    }, 1000);

});