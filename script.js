
const $ = (id) => document.getElementById(id);
const user = (id) => ($(id)?.value ?? "").trim();
const err = (id, msg = "") => { const e = $(id); if (e) e.textContent = msg; };
const clearErr = () => document.querySelectorAll(".error-message").forEach(e => e.textContent = "");
const emailOk = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(s);

function passwordOk(p) {
  if (p.length < 8 || p.length > 20) return "Password must be 8–20 characters.";
  if (!/[a-z]/.test(p)) return "Password needs a lowercase letter.";
  if (!/[A-Z]/.test(p)) return "Password needs an uppercase letter.";
  if (!/\d/.test(p)) return "Password needs a number.";
  if (!/[!@#$%^&*()_\-+=\[\]{};:'",.<>/?\\|`~]/.test(p)) return "Password needs a special character.";
  if (/\s/.test(p)) return "Password must not contain spaces.";
  return "";
}


function regData() {
  return {
    username: user("username"),
    password: user("password"),
    name: user("Name") || user("name"),
    zipCode: user("ZipCode") || user("zipCode"),
    country: $("country")?.value || "",
    email: user("email").toLowerCase(),
    sex: document.querySelector('input[name="sex"]:checked')?.value || "",
    languages: [...document.querySelectorAll('input[name="language"]:checked')].map(x => x.value),
    addr: user("address"),
    about: user("about"),
  };
}

function loginData() {
  return {
    email: user("email").toLowerCase(),
    password: user  ("password"),
    agreement: $("agreement")?.checked || false,
  };
}


function validateReg(userRegister) {
  let ok = true;

  if (userRegister.username.length < 5 || userRegister.username.length > 12) (err("usernameError","Username must be 5–12 chars."), ok=false);

  const pmsg = passwordOk(userRegister.password);
  if (pmsg) (err("passwordError", pmsg), ok=false);

  if (!userRegister.name || !/^[a-zA-Z\s]+$/.test(userRegister.name)) (err("nameError","Name: letters & spaces only."), ok=false);

  if (!userRegister.country) (err("countryError","Please select a country."), ok=false);

  if (!userRegister.zipCode || !/^\d+$/.test(userRegister.zipCode)) (err("zipCodeError","Zip: digits only."), ok=false);

  if (!userRegister.email || !emailOk(userRegister.email)) (err("emailError","Enter a valid email."), ok=false);

  if (!userRegister.sex) (err("sexError","Required."), ok=false);

  if (!userRegister.languages.length) (err("languageError","Required."), ok=false);

  return ok;
}


const KEY = "simpleUser";
const saveUser = (u) => localStorage.setItem(KEY, JSON.stringify(u));
const loadUser = () => {
  const s = localStorage.getItem(KEY);
  return s ? JSON.parse(s) : null;
};


function onRegister(e, form) {
  e.preventDefault();
  clearErr();

  const userRegister = regData();
  if (!validateReg(userRegister)) return;

  saveUser(userRegister);
  alert("Registration successful!");
  form.reset();
}

function onLogin(e, form) {
  e.preventDefault();
  clearErr();

  const d = loginData();
  const user = loadUser();

  if (!user) return err("loginError", "No registered user. Please register first.");
  if (!d.agreement) return err("agreementError", "You must agree to the terms.");


  if (d.email !== (user.email || "").toLowerCase()) return err("loginError", "Email not correct.");
  if (d.password !== (user.password || "")) return err("loginError", "Password not correct.");

  alert("Login successful!");
  form.reset();
}


function init() {
  const reg = document.querySelector(".RegistrationSection form");
  if (reg) reg.addEventListener("submit", (e) => onRegister(e, reg));

  const log = document.querySelector(".LoginSection form");
  if (log) log.addEventListener("submit", (e) => onLogin(e, log));
}

document.addEventListener("DOMContentLoaded", init);