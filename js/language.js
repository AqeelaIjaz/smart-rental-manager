// =====================================
// SMART RENTAL MANAGER
// Language Switcher
// =====================================


function changeLanguage(language){


    localStorage.setItem(
        "language",
        language
    );



    // Translate all elements
    document.querySelectorAll("[data-key]").forEach(function(element){


        let key = element.getAttribute("data-key");


        if(translations[language][key]){


            element.textContent = translations[language][key];


        }


    });





    // Change direction

    if(language === "ur"){


        document.documentElement.lang = "ur";

        document.body.dir = "rtl";


    }
    else{


        document.documentElement.lang = "en";

        document.body.dir = "ltr";


    }





    // Active button styling

    let englishBtn = document.getElementById("englishBtn");
    let urduBtn = document.getElementById("urduBtn");


    if(englishBtn && urduBtn){


        if(language === "ur"){


            urduBtn.classList.add("active");

            englishBtn.classList.remove("active");


        }
        else{


            englishBtn.classList.add("active");

            urduBtn.classList.remove("active");


        }


    }


}









document.addEventListener(
"DOMContentLoaded",
function(){



    let savedLanguage = 
    localStorage.getItem("language") || "en";



    changeLanguage(savedLanguage);





    // Welcome Page Buttons

    let englishBtn = document.getElementById("englishBtn");

    let urduBtn = document.getElementById("urduBtn");




    if(englishBtn){


        englishBtn.addEventListener(
            "click",
            function(){

                changeLanguage("en");

            }
        );


    }






    if(urduBtn){


        urduBtn.addEventListener(
            "click",
            function(){

                changeLanguage("ur");

            }
        );


    }





});