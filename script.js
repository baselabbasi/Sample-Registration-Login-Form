function $(id) {
  return document.getElementById(id);
}

function setError(id, text) {
  const errorElement = $(id);
  if (errorElement) errorElement.textContent = text;
}
function clearError(id) {
  document
    .querySelectorAll(".error-message")
    .forEach((element) => (element.textContent = ""));
}
function isValidEmail(email) {
  return email.includes("@") && email.includes(".");
}

/* Registration Page */
function initRegistration() {
  const regForm = document.querySelector(".RegistrationSection form");
  if (!regForm) return;

  regForm.addEventListener("submit", function (event) {
    event.preventDefault();
    clearError();

    const userId = $("username");
    const password = $("password");
    const name = $("Name") || $("name");
    const zipCode = $("ZipCode") || $("zipCode");
    const email = $("email");

    let ok = true;

    const uid = userId ? userId.value.trim() : "";
    if (uid.length < 5 || uid.length > 12) {
      setError(
        "usernameError",
        "Username must be between 5 and 12 characters.",
      );
      ok = false;
    }
    const pwd = password ? password.value.trim() : "";
    if (pwd.length < 7 || pwd.length > 12) {
      setError(
        "passwordError",
        "Password must be between 7 and 12 characters.",
      );
      ok = false;
    }
    const nm = name ? name.value.trim() : "";
    if (!nm || !/^[a-zA-Z\s]+$/.test(nm)) {
      setError(
        "nameError",
        "Required. Name must contain only letters and spaces.",
      );
      ok = false;
    }
    const addr = $("address");
    const country = $("country");
    if (!country || !country.value) {
      setError("countryError", "Required. Please select a country.");
      ok = false;
    }
    const zp = zipCode ? zipCode.value.trim() : "";
    if (!zp || !/^\d+$/.test(zp)) {
      setError("zipCodeError", "Required. Zip Code must contain only digits.");
      ok = false;
    }
    const eml = email ? email.value.trim() : "";
    if (!eml || !isValidEmail(eml)) {
      setError("emailError", "Please enter a valid email address.");
      ok = false;
    }
    const sex = document.querySelector('input[name="sex"]:checked');
    if (!sex) {
      setError("sexError", "Required. ");
      ok = false;
    }

    const language = document.querySelectorAll(
      'input[name="language"]:checked',
    );
    if (!language || language.length === 0) {
      setError("languageError", "Required.");
      ok = false;
    }
    const about = $("about");

    if (ok) {
      const user = {
        username: uid,
        password: pwd,
        name: nm,
        zipCode: zp,
        country: country.value,
        email: eml,
        sex: sex.value,
        addr:   addr ? addr.value.trim() : "",
        about: about ? about.value.trim() : "",
        languages: Array.from(language).map((l) => l.value),
      };
        localStorage.setItem("simpleUser", JSON.stringify(user));

      alert("Registration successful!");
      regForm.reset();
    }
  });
}

function initLogin() {
  const loginForm = document.querySelector(".LoginSection form");
  if (!loginForm) return;

  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();
    clearError();       

    const email = $("email");
    const password = $("password"); 
    const agreement = $("agreement");

    const saved = localStorage.getItem("simpleUser");
    if (!saved) {
        setError("loginError", "No registered user found. Please register first."); 
        return;
    }
     if (!agreement || !agreement.checked) {
      setError("agreementError", "You must agree to the terms and conditions.");
      return;
    }

    const user = JSON.parse(saved);
    const eml = email ? email.value.trim().toLowerCase() : "";
    const pwd = password ? password.value : "";

    if(eml === user.email)
    {
        setError("loginError", "Email not correct.");
    return;
    }
    if(pwd === user.password)
    {
        setError("loginError", "Password not correct.");
    return;
    }
    alert("Login successful!");
    loginForm.reset();
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initRegistration();
  initLogin();
});