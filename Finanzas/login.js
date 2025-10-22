let btn;
let user = document.getElementById("user");
let pass = document.getElementById("pass");

document.getElementById("btn").addEventListener("click", function(event){
    event.preventDefault();
    if(user.value === "orlynk" && pass.value === "12345678"){
        alert("Inicio de sesion exitoso");
        let saludoUser = user.value;
        window.location.href = "menuPrincipal.html";
    } else {
        alert("Usuario o contraseña incorrectos");
        user.value = "";
        pass.value = "";

    }});

    console.log("Script cargado correctamente");
