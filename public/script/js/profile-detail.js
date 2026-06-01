document.addEventListener("DOMContentLoaded", () => {
	// =========================
	// CAROUSEL PHOTOS
	// =========================

	document.querySelectorAll(".photo-thumbnail").forEach((img) => {
		img.addEventListener("click", () => {
			const index = parseInt(img.dataset.index);

			const modal = document.querySelector(img.dataset.bsTarget);

			if (!modal) return;

			const carouselElement = modal.querySelector(".carousel");

			if (!carouselElement) return;

			const carousel = new bootstrap.Carousel(carouselElement, {
				interval: false,
				wrap: true,
				touch: true,
			});

			setTimeout(() => {
				carousel.to(index);
			}, 200);
		});
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
