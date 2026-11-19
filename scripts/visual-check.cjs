const { mkdir } = require('node:fs/promises');
const { resolve, join } = require('node:path');
const { chromium } = require('playwright');

async function main() {
    const baseUrl = (process.env.BASE_URL || 'http://localhost/glowbella').replace(/\/+$/, '');
    const routes = (process.argv.slice(2).length ? process.argv.slice(2) : ['esterilizacao/'])
        .map(route => route.replace(/^\/+/, ''));
    const outputDirectory = resolve(__dirname, '..', 'artifacts', 'visual');
    const viewports = [
        { name: 'desktop', width: 1440, height: 1000, isMobile: false },
        { name: 'mobile', width: 390, height: 844, isMobile: true },
    ];

    await mkdir(outputDirectory, { recursive: true });

    const launchOptions = { headless: true };
    if (process.env.PLAYWRIGHT_EXECUTABLE_PATH) {
        launchOptions.executablePath = process.env.PLAYWRIGHT_EXECUTABLE_PATH;
    }

    const browser = await chromium.launch(launchOptions);
    const problems = [];

    try {
        for (const route of routes) {
            const routeSlug = route.replace(/\/+$/, '').replace(/\.php$/i, '').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
            const pageUrl = `${baseUrl}/${route}`;

            for (const viewport of viewports) {
                const label = `${routeSlug}/${viewport.name}`;
                const page = await browser.newPage({
                    viewport: { width: viewport.width, height: viewport.height },
                    isMobile: viewport.isMobile,
                    hasTouch: viewport.isMobile,
                });
                const pageErrors = [];
                const stylesheetFailures = [];

                page.on('pageerror', error => pageErrors.push(error.message));
                page.on('requestfailed', request => {
                    if (request.resourceType() === 'stylesheet') {
                        stylesheetFailures.push(`${request.url()} (${request.failure()?.errorText || 'falha'})`);
                    }
                });

                const response = await page.goto(pageUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
                if (!response || !response.ok()) {
                    problems.push(`${label}: a página respondeu ${response?.status() ?? 'sem resposta'}.`);
                }

                const bootstrapLoaded = await page.waitForFunction(() => {
                    const row = document.querySelector('.row');
                    if (row) return getComputedStyle(row).display === 'flex';

                    const button = document.querySelector('.btn');
                    return button && getComputedStyle(button).paddingTop === '6px';
                }, null, { timeout: 15000 }).then(() => true).catch(() => false);
                await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
                await page.evaluate(() => document.fonts.ready);

                const metrics = await page.evaluate(() => ({
                    title: document.title,
                    viewportWidth: window.innerWidth,
                    documentWidth: document.documentElement.scrollWidth,
                }));

                const screenshotPath = join(outputDirectory, `${routeSlug}-${viewport.name}.png`);
                await page.screenshot({ path: screenshotPath, fullPage: true });

                console.log(`${label}: ${pageUrl}`);
                console.log(`  captura: ${screenshotPath}`);
                console.log(`  título: ${metrics.title}; Bootstrap: ${bootstrapLoaded ? 'carregado' : 'não carregado'}`);
                console.log(`  largura: conteúdo ${metrics.documentWidth}px / tela ${metrics.viewportWidth}px`);

                if (!bootstrapLoaded) {
                    problems.push(`${label}: Bootstrap não carregou; a captura não representa o layout real.`);
                }
                if (metrics.documentWidth > metrics.viewportWidth + 1) {
                    problems.push(`${label}: há rolagem horizontal (${metrics.documentWidth}px para tela de ${metrics.viewportWidth}px).`);
                }
                if (pageErrors.length) {
                    problems.push(`${label}: erros JavaScript: ${pageErrors.join('; ')}`);
                }
                if (stylesheetFailures.length) {
                    problems.push(`${label}: folhas de estilo não carregadas: ${stylesheetFailures.join('; ')}`);
                }

                await page.close();
            }
        }
    } finally {
        await browser.close();
    }

    if (problems.length) {
        console.error('\nConferência visual encontrou problemas:');
        for (const problem of problems) console.error(`- ${problem}`);
        process.exitCode = 1;
    } else {
        console.log('\nConferência visual concluída sem erros de página, estilos ausentes ou rolagem horizontal.');
    }
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
