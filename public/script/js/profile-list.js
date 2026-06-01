document.addEventListener("DOMContentLoaded", () => {
	document.querySelectorAll(".photo-thumbnail").forEach((img) => {
		img.addEventListener("click", () => {
			const index = parseInt(img.dataset.index);

			const modalId = img.dataset.bsTarget;
			const modal = document.querySelector(modalId);

			if (!modal) return;

			const carouselElement = modal.querySelector(".carousel");

			if (!carouselElement) return;

			const carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement);

			carousel.to(index);
		});
	});
});
