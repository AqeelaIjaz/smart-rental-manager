// ===========================================
// Smart Rental Manager
// OTP Verification JavaScript
// ===========================================


const otpInputs = document.querySelectorAll(".otp-input");


// Move automatically to next OTP box

otpInputs.forEach((input, index) => {

    input.addEventListener("input", () => {

        if(input.value.length === 1 && index < otpInputs.length - 1){

            otpInputs[index + 1].focus();

        }

    });


    // Move back when deleting

    input.addEventListener("keydown",(e)=>{

        if(e.key === "Backspace" && input.value === "" && index > 0){

            otpInputs[index - 1].focus();

        }

    });

});



// OTP Form Submit

const otpForm = document.getElementById("otpForm");


otpForm.addEventListener("submit",(e)=>{

    e.preventDefault();


    let otp = "";


    otpInputs.forEach(input=>{

        otp += input.value;

    });



    if(otp.length !== 6){

        alert("Please enter the complete 6-digit OTP.");

        return;

    }



    // Temporary frontend verification

    alert("OTP Verified Successfully!");



    setTimeout(()=>{

        window.location.href="reset-password.html";

    },1000);


});




// Countdown Timer

let time = 60;


const countdown = document.getElementById("countdown");



const timer = setInterval(()=>{


    let seconds = time;


    if(seconds < 10){

        seconds = "0" + seconds;

    }


    countdown.innerHTML = "00:" + seconds;



    time--;



    if(time < 0){

        clearInterval(timer);

        countdown.innerHTML = "Expired";

    }



},1000);




// Resend OTP


const resendOTP = document.getElementById("resendOTP");


resendOTP.addEventListener("click",(e)=>{


    e.preventDefault();


    alert("A new OTP has been sent.");



    time = 60;


});