// ===========================================
// Smart Rental Manager
// Agreement Upload JavaScript
// ===========================================



const fileInput = document.getElementById("agreementFile");

const uploadBox = document.getElementById("uploadBox");

const filePreview = document.getElementById("filePreview");

const submitButton = document.getElementById("submitAgreement");




// ===============================
// Open File Selector
// ===============================


uploadBox.addEventListener("click",()=>{


    fileInput.click();


});





// ===============================
// Display Selected File
// ===============================


fileInput.addEventListener("change",()=>{


    const file = fileInput.files[0];



    if(file){


        filePreview.innerHTML = `

        <i class="fa-solid fa-file-circle-check"></i>

        <p>
            ${file.name}
        </p>

        `;


    }


});





// ===============================
// Upload Agreement
// ===============================


submitButton.addEventListener("click",()=>{



    const file = fileInput.files[0];



    if(!file){


        alert("Please select an agreement file first.");

        return;


    }



    // Simulating AI extraction


    alert(
        "Agreement uploaded successfully. AI is extracting details..."
    );



    setTimeout(()=>{



        const details = document.querySelectorAll(".detail b");



        details[0].innerHTML = "Rs. 35,000";

        details[1].innerHTML = "5th Every Month";

        details[2].innerHTML = "Late payment penalty included";



        alert(
            "AI extraction completed!"
        );



    },2000);



});





// ===============================
// Drag and Drop Support
// ===============================


uploadBox.addEventListener("dragover",(e)=>{


    e.preventDefault();


    uploadBox.style.background="#F5EFE6";


});




uploadBox.addEventListener("dragleave",()=>{


    uploadBox.style.background="white";


});





uploadBox.addEventListener("drop",(e)=>{


    e.preventDefault();



    fileInput.files = e.dataTransfer.files;



    const file = fileInput.files[0];



    if(file){


        filePreview.innerHTML = `

        <i class="fa-solid fa-file-circle-check"></i>

        <p>
            ${file.name}
        </p>

        `;


    }



});