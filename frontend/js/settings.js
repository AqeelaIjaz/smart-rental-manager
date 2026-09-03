// ===========================================
// Smart Rental Manager
// Settings JavaScript
// ===========================================


document.addEventListener("DOMContentLoaded", function(){


const english = document.getElementById("english");
const urdu = document.getElementById("urdu");

const rtlToggle = document.getElementById("rtlToggle");

const saveSettings = document.getElementById("saveSettings");




// ===============================
// Load Saved Language
// ===============================


let savedLanguage = localStorage.getItem("language") || "en";


if(savedLanguage === "ur"){


    urdu.checked = true;

    changeLanguage("ur");

    document.body.dir = "rtl";


}
else{


    english.checked = true;

    changeLanguage("en");

    document.body.dir = "ltr";


}






// ===============================
// Urdu Selection
// ===============================


urdu.addEventListener("change", function(){


    if(urdu.checked){


        changeLanguage("ur");


        document.body.dir = "rtl";


        localStorage.setItem(
            "language",
            "ur"
        );


    }


});







// ===============================
// English Selection
// ===============================


english.addEventListener("change", function(){


    if(english.checked){


        changeLanguage("en");


        document.body.dir = "ltr";


        localStorage.setItem(
            "language",
            "en"
        );


    }


});









// ===============================
// RTL Toggle
// ===============================


rtlToggle.addEventListener("change", function(){



    if(rtlToggle.checked){


        document.body.dir = "rtl";


    }

    else{


        document.body.dir = "ltr";


    }



});










// ===============================
// Save Settings
// ===============================


saveSettings.addEventListener("click", function(){



    let selectedLanguage;



    if(urdu.checked){


        selectedLanguage = "ur";


    }

    else{


        selectedLanguage = "en";


    }





    localStorage.setItem(
        "language",
        selectedLanguage
    );





    changeLanguage(selectedLanguage);






    if(selectedLanguage === "ur"){


        alert("سیٹنگز محفوظ ہوگئیں");


    }
    else{


        alert("Settings saved successfully!");


    }



});



});