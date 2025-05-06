document.addEventListener("DOMContentLoaded", function () {
  // Les filtres sont maintenant toujours visibles, pas besoin de toggle
  let sortContent = document.querySelector(".sort-content");

  // Tu n'as plus besoin de gestion du toggle pour les filtres, donc ce bloc est supprimé

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
