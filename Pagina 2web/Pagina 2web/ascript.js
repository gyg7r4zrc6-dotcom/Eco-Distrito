/* =========================
   MENÚ RESPONSIVE
========================= */

function abrirMenu(){

    const menu =
        document.querySelector(".menu");

    if(menu){

        menu.classList.toggle("abierto");

    }

}


/* =========================
   FORMULARIO DE DENUNCIA
========================= */

const formulario =
    document.getElementById("formDenuncia");


if(formulario){

    formulario.addEventListener(
        "submit",
        async function(event){

            event.preventDefault();

            const resultado =
                document.getElementById("resultado");

            resultado.style.color =
                "#145c7a";

            resultado.textContent =
                "⏳ Registrando denuncia...";


            const datos =
                new FormData(formulario);


            try{

                const respuesta =
                    await fetch(
                        formulario.action,
                        {
                            method:"POST",
                            body:datos
                        }
                    );


                const informacion =
                    await respuesta.json();


                if(!respuesta.ok ||
                   !informacion.ok){

                    throw new Error(
                        informacion.message ||
                        "No fue posible registrar la denuncia."
                    );

                }


                resultado.style.color =
                    "#2f6b45";

                resultado.textContent =
                    "✅ " +
                    informacion.message;


                formulario.reset();


            }catch(error){

                resultado.style.color =
                    "#b83a3a";

                resultado.textContent =
                    "❌ " +
                    error.message;

            }

        }
    );

}


/* =========================
   ANIMACIÓN AL APARECER
========================= */

const elementos =
    document.querySelectorAll(
        ".tarjeta, .penalizacion, .contacto"
    );


const observador =
    new IntersectionObserver(
        function(elementos){

            elementos.forEach(
                function(elemento){

                    if(elemento.isIntersecting){

                        elemento.target.style.opacity =
                            "1";

                        elemento.target.style.transform =
                            "translateY(0)";

                    }

                }
            );

        },
        {
            threshold:0.1
        }
    );


elementos.forEach(
    function(elemento){

        elemento.style.opacity = "0";

        elemento.style.transform =
            "translateY(20px)";

        elemento.style.transition =
            "opacity .6s ease, transform .6s ease";

        observador.observe(elemento);

    }
);