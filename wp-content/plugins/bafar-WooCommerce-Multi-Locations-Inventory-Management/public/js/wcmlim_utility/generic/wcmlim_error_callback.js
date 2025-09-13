// Define callback function for failed attempt
export function errorCallback(error) {
  if (error.code == 1) {
    Swal.fire({
      icon: "error",
      text: "Has decidido no compartir tu ubicación, pero está bien. No volveremos a pedirlo.",
    });
    alies_setcookies.setcookie("wcmlim_nearby_location", " ");
    setCookie('geolocation_accepted', '');
    return;
  } else if (error.code == 2) {
    Swal.fire({
      icon: "error",
      text: "La red está caída o el servicio de posicionamiento no puede ser alcanzado. Has decidido no compartir tu ubicación, pero está bien. No volveremos a pedirlo.",
    });
    setCookie('geolocation_accepted', '');
    return;
  } else if (error.code == 3) {
    Swal.fire({
      icon: "error",
      text: "El intento expiró antes de poder obtener los datos de ubicación.",
    });
    setCookie('geolocation_accepted', '');
    return;
  } else {
    Swal.fire({
      icon: "error",
      text: "La geolocalización falló debido a un error desconocido.",
    });
    setCookie('geolocation_accepted', '');
    return;
  }
  localStorage.setItem("dialogShown", 1);
}
