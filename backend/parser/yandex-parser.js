const { chromium } = require('playwright');

(async () => {

    const url = process.argv[2];

    const browser = await chromium.launch({
        headless: true
    });

    const page = await browser.newPage();

    await page.goto(url, {
        waitUntil: 'networkidle',
        timeout: 60000
    });

    // Load more reviews
    for (let i = 0; i < 30; i++) {
        await page.mouse.wheel(0, 5000);
        await page.waitForTimeout(1000);

        console.error(
    `Scrolled ${i + 1}`
);
    }

    const reviews = await page.evaluate(() => {

        return Array.from(
            document.querySelectorAll(
                '.business-reviews-card-view__review'
            )
        ).map(review => {

            const author =
                review.querySelector(
                    '.business-review-view__author-name [itemprop="name"]'
                )?.textContent?.trim() || '';

            const date =
                review.querySelector(
                    '.business-review-view__date'
                )?.textContent?.trim() || '';

            const text =
                review.querySelector(
                    '.business-review-view__body'
                )?.textContent?.trim() || '';

            const rating = review.querySelectorAll(
                '.business-rating-badge-view__star._full'
            ).length;

            const likes =
                review.querySelector(
                    '.business-reactions-view__container:first-child .business-reactions-view__counter'
                )?.textContent?.trim() || '0';

            return {
                author,
                rating,
                date,
                text,
                likes
            };
        });

    });

   console.log(JSON.stringify({
    reviews
}));

    await browser.close();

})();
