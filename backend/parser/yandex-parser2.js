const { chromium } = require('playwright');

(async () => {
    const url = process.argv[2];

    const browser = await chromium.launch({
        headless: true
    });

    const page = await browser.newPage();

    let allReviews = [];

    //  intercept  API responses
    page.on('response', async (response) => {
        const resUrl = response.url();

        if (resUrl.includes('fetchReviews')) {
            try {
                const json = await response.json();

                const reviews = json?.data?.reviews || [];

                for (const r of reviews) {
                    allReviews.push({
                        author: r?.author?.name || '',
                        rating: r?.rating || 0,
                        date: r?.updatedTime || '',
                        text: r?.text || '',
                        likes: r?.likes || 0
                    });
                }
            } catch (e) {}
        }
    });

    await page.goto(url, {
          waitUntil: 'networkidle',
          timeout: 60000
    });
 // Load more reviews
    for (let i = 0; i < 25; i++) {
        await page.mouse.wheel(0, 5000);
        await page.waitForTimeout(1000);
    }

    // await page.waitForTimeout(5000);

    console.log(JSON.stringify({ reviews: allReviews }));

    await browser.close();
})();
