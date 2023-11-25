/*
This script ensures on client-side that there are no logos > 1MB and no images > 2MB uploaded to the server by:
- highlighting the respective file input as invalid
- disabling the submit button
This is done to improve the User Experience - the same validation and more will be done server-side anyway
Requirements for HTML: the id attributes of the HTML elements must be `inputLogo`, `inputImage` and `submitButton`
 */

const logoInput = document.getElementById("inputLogo");
const imageInput = document.getElementById("inputImage");
const submitButton = document.getElementById("submitButton");

// both images (if present) must have a valid size for the submit button to be clickable
let logoSizeValid = true, imageSizeValid = true;

// logo file may only be 1MB
logoInput.addEventListener("change", function () {
    if (logoInput.files.length === 0) {
        logoSizeValid = true; submitButton.disabled = !(logoSizeValid && imageSizeValid);
        logoInput.classList.remove("is-invalid");
    } else if (logoInput.files.length === 1) {
        const fileSize = logoInput.files.item(0).size;
        const fileMb = fileSize / 1000**2;
        if (fileMb > 1) {
            logoSizeValid = false; submitButton.disabled = true;
            logoInput.classList.add("is-invalid");
        } else {
            logoSizeValid = true; submitButton.disabled = !(logoSizeValid && imageSizeValid);
            logoInput.classList.remove("is-invalid");
        }
    }
});

// image file may only be 2MB
imageInput.addEventListener("change", function () {
    if (imageInput.files.length === 0) {
        imageSizeValid = true; submitButton.disabled = !(logoSizeValid && imageSizeValid);
        imageInput.classList.remove("is-invalid");
    } else if (imageInput.files.length === 1) {
        const fileSize = imageInput.files.item(0).size;
        const fileMb = fileSize / 1000**2;
        if (fileMb > 2) {
            imageSizeValid = false; submitButton.disabled = true;
            imageInput.classList.add("is-invalid");
        } else {
            imageSizeValid = true; submitButton.disabled = !(logoSizeValid && imageSizeValid);
            imageInput.classList.remove("is-invalid");
        }
    }
});