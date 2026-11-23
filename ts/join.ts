import './join.scss';


$(() => {
    $<HTMLInputElement>('#password-visibility').on('change', function (ev) {
        ev.preventDefault();
        
        console.log(`checkbox ${ev.target.name} changed to ${ev.target.checked}`);

        const passwordField = document.querySelector("#password") as HTMLInputElement | null;       
    
        if (passwordField != null) {
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }       
    });
});