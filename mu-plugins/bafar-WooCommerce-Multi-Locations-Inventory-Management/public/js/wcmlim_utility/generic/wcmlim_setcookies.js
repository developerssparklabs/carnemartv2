export function setcookie(name, value, days) {
  let date = new Date();
  if (days) {
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    var expires = `; expires=${date.toUTCString()}`;
  } else {
    date.setTime(date.getTime() + 1 * 24 * 60 * 60 * 1000);
    var expires = `; expires=${date.toUTCString()}`;
  }
  document.cookie = `${name}=${value}${expires};path=/`;
}
