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





// ===============================
// Voice Recording Simulation
// ===============================


recordBtn.addEventListener("click",()=>{


    if(!isRecording){



        isRecording = true;



        recordBtn.classList.add("recording");



        recordStatus.innerHTML =
        "Recording... Speak your complaint";



        recordBtn.innerHTML =
        '<i class="fa-solid fa-stop"></i>';



    }

    else{


        isRecording = false;



        recordBtn.classList.remove("recording");



        recordStatus.innerHTML =
        "Recording completed";



        recordBtn.innerHTML =
        '<i class="fa-solid fa-microphone"></i>';




        // Demo speech-to-text result


        complaintText.value =
        "The kitchen tap is leaking and needs repair.";



        aiResponse.innerHTML =
        "Based on the complaint, this appears to be a maintenance issue. The landlord should arrange repair within a reasonable time.";





    }



});








// ===============================
// Submit Complaint
// ===============================


submitComplaint.addEventListener("click",()=>{


    if(complaintText.value.trim()===""){


        alert(
            "Please record or write your complaint first."
        );


        return;


    }



    alert(
        "Complaint submitted successfully!"
    );



    setTimeout(()=>{


        window.location.href="dashboard.html";


    },1000);



});