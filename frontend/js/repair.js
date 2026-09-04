// ===========================================
// Smart Rental Manager
// Repair Report JavaScript
// ===========================================



const repairImage = document.getElementById("repairImage");

const photoBox = document.getElementById("photoBox");

const imagePreview = document.getElementById("imagePreview");

const submitRepair = document.getElementById("submitRepair");

const issueDescription = document.getElementById("issueDescription");

const priority = document.getElementById("priority");





// ===============================
// Open Image Selector
// ===============================


photoBox.addEventListener("click",()=>{


    repairImage.click();


});





// ===============================
// Image Preview
// ===============================


repairImage.addEventListener("change",()=>{


    const image = repairImage.files[0];



    if(image){


        imagePreview.innerHTML = `

        <i class="fa-solid fa-image"></i>

        <p>
            ${image.name}
        </p>

        `;


    }


});







// ===============================
// Repair Analysis (rule-based)
// ===============================


function analyzeRepair(){



    const aiIssue = document.getElementById("aiIssue");

    const aiPriority = document.getElementById("aiPriority");

    const aiCost = document.getElementById("aiCost");




    aiIssue.innerHTML =
    "Water leakage / damaged fixture";



    aiPriority.innerHTML =
    priority.value === "Select Priority"
    ? "Medium"
    : priority.value;



    aiCost.innerHTML =
    "Estimated Rs. 3,000 - 5,000";



}





// Run repair analysis when details change


priority.addEventListener("change",()=>{


    analyzeRepair();


});



issueDescription.addEventListener("keyup",()=>{


    if(issueDescription.value.length > 5){

        analyzeRepair();

    }


});







// ===============================
// Submit Repair Request
// ===============================



    submitRepair.addEventListener("click", async () => {

    if(!repairImage.files[0]){
        alert("Please upload an image first.");
        return;
    }

    if(issueDescription.value.trim()===""){
        alert("Please describe the issue.");
        return;
    }

    const formData = new FormData();
    formData.append("photo", repairImage.files[0]);
    formData.append("issue_description", issueDescription.value);
    formData.append("priority", priority.value.toLowerCase());
    formData.append("estimated_cost", "4000"); // ya jo bhi fixed price list se aaye
    formData.append("agreement_id", 1); // actual logged-in tenant ka agreement_id yahan aana chahiye

    try {
        const response = await fetch("../../backend/repairs/create.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if(result.success){
            alert("Repair request submitted successfully!");
            setTimeout(()=>{ window.location.href="dashboard.html"; }, 1000);
        } else {
            alert("Submission failed: " + result.message);
        }

    } catch(err){
        alert("Error connecting to server.");
        console.error(err);
    }
});









// ===============================
// Drag and Drop Image
// ===============================


photoBox.addEventListener("dragover",(e)=>{


    e.preventDefault();


    photoBox.style.background="#F5EFE6";


});





photoBox.addEventListener("dragleave",()=>{


    photoBox.style.background="white";


});





photoBox.addEventListener("drop",(e)=>{


    e.preventDefault();


    repairImage.files=e.dataTransfer.files;


    const image = repairImage.files[0];



    if(image){


        imagePreview.innerHTML=`


        <i class="fa-solid fa-image"></i>


        <p>
            ${image.name}
        </p>


        `;


    }


});