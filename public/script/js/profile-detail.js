document.addEventListener("DOMContentLoaded", () => {
	// =========================
	// CAROUSEL PHOTOS
	// =========================
	const lightbox = document.getElementById("photoCarousel");
	const images = Array.from(document.querySelectorAll(".profile-lightbox-image"));
	const closeButton = document.getElementById("closePhotoCarousel");
	const previousButton = document.getElementById("profileCarouselPrevious");
	const nextButton = document.getElementById("profileCarouselNext");
	let currentIndex = 0;

	const displayImage = (index) => {
		currentIndex = (index + images.length) % images.length;
		images.forEach((image, imageIndex) => {
			image.classList.toggle("is-active", imageIndex === currentIndex);
		});
	};

	const closeLightbox = () => {
		lightbox.classList.remove("is-open");
		lightbox.setAttribute("aria-hidden", "true");
		document.body.classList.remove("lightbox-open");
	};

	document.querySelectorAll(".js-profile-photo").forEach((photo) => {
		photo.addEventListener("click", () => {
			displayImage(Number(photo.dataset.photoIndex));
			lightbox.classList.add("is-open");
			lightbox.setAttribute("aria-hidden", "false");
			document.body.classList.add("lightbox-open");
		});
	});

	previousButton?.addEventListener("click", () => displayImage(currentIndex - 1));
	nextButton?.addEventListener("click", () => displayImage(currentIndex + 1));
	closeButton?.addEventListener("click", closeLightbox);

	lightbox?.addEventListener("click", (event) => {
		if (event.target === lightbox) closeLightbox();
	});

	// =========================
	// LIKE AJAX
	// =========================

	document.querySelectorAll(".js-like-btn").forEach((btn) => {
		btn.addEventListener("click", async () => {
			const url = btn.dataset.url;
			const token = btn.dataset.token;

			const alertBox = document.getElementById("like-alert");

			try {
				const response = await fetch(url, {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded",
					},
					body: new URLSearchParams({
						_token: token,
					}),
				});

				const data = await response.json();

				alertBox.style.display = "block";

				if (data.message === "Profil liké avec succès") {
					alertBox.innerHTML = "✅ Vous avez bien liké ce profil.";

					alertBox.className = "like-alert success";

					btn.innerHTML = "❤️ Liké";
					btn.disabled = true;
				} else if (data.message === "Déjà liké") {
					alertBox.innerHTML = "ℹ️ Vous avez déjà liké ce profil.";

					alertBox.className = "like-alert info";

					btn.innerHTML = "❤️ Déjà liké";
					btn.disabled = true;
				} else {
					alertBox.innerHTML = "❌ Une erreur est survenue.";

					alertBox.className = "like-alert error";
				}

				setTimeout(() => {
					alertBox.style.display = "none";
				}, 4000);
			} catch (error) {
				alertBox.style.display = "block";

				alertBox.innerHTML = "❌ Une erreur est survenue.";

				alertBox.className = "like-alert error";

				setTimeout(() => {
					alertBox.style.display = "none";
				}, 4000);
			}
		});
	});
});
