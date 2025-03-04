document.addEventListener("DOMContentLoaded", function () {
  // Gestion du filtre
  let toggleButton = document.querySelector(".toggle-sort-btn");
  let sortContent = document.querySelector(".sort-content");

  if (sortContent) {
    sortContent.style.display = "none";
    if (toggleButton) {
      toggleButton.addEventListener("click", function () {
        sortContent.style.display =
          sortContent.style.display === "none" ? "block" : "none";
      });
    }
  }

  // Gestion de l'affichage des photos pour chaque profil
  document.querySelectorAll(".toggle-photos-btn").forEach((button) => {
    button.addEventListener("click", function () {
      let profileId = this.getAttribute("data-profile-id");
      let gallery = document.getElementById("gallery-" + profileId);

      if (gallery) {
        let isHidden =
          gallery.style.display === "none" || gallery.style.display === "";
        gallery.style.display = isHidden ? "flex" : "none";
        this.textContent = isHidden
          ? "Masquer les photos"
          : "Afficher les photos";
      }
    });
  });

  // Gestion du modal pour afficher l'image cliquée en premier
  document.querySelectorAll(".photo-thumbnail").forEach((img, index) => {
    img.addEventListener("click", function () {
      let carousel = new bootstrap.Carousel(
        document.querySelector("#carouselExample")
      );
      carousel.to(index);
    });
  });
});
