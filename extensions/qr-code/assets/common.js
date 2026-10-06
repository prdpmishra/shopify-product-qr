document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("product-qr-code");

    if (!container) return;

    const productId = container.getAttribute("data-product-id");
    const defaultVariantId = container.getAttribute("data-default-variant-id");

    fetchData(
        `/apps/qr-code/qr_code?product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(defaultVariantId)}`,
    );

    document.addEventListener(
        "change",
        (event) => {
            const input = event.target;

            // Listen to the variant ID input, not the option picker.
            if (!(input instanceof HTMLInputElement)) return;
            if (!input.matches('form[action*="/cart/add"] input[name="id"]')) {
                return;
            }

            // Only handle the product section containing this QR code.
            const section = container.closest('[id^="shopify-section-"]');
            if (!section || !section.contains(input)) return;

            const variantId = input.value;
            if (!variantId) return;

            fetchData(
                `/apps/qr-code/qr_code?product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(variantId)}`,
            );
        },
        true,
    );

    async function fetchData(url) {
        try {
            const response = await fetch(url);

            // 1. Check if the HTTP status code is in the 200–299 range
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            // 2. Parse the body data based on content type (e.g., JSON)
            const data = await response.json();

            if (data.message) {
                container.innerHTML = `<strong>Scan QR code to checkout:</strong><br /> <img src="${data.message}" alt="QR Code" class="qr" />`;
            } else {
                container.innerHTML = "";
            }
        } catch (error) {
            // Catches both network errors and thrown HTTP errors
            console.error("Fetch failed:", error.message);
        }
    }
});
