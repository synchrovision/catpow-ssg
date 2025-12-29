import fs from "fs";
import { test, expect } from "@playwright/test";

test("screenshot", async ({ page, browser }, testInfo) => {
	const index = await fetch("http://localhost:8000/_compiler/api/index/").then((res) => res.json());
	console.log("Crawling", index.length, "pages");
	for (let uri of index) {
		if (uri.includes("://")) continue;
		console.log("Crawling", uri);
		testInfo.setTimeout(testInfo.timeout + 6000);
		await page.goto(`http://localhost:8000${uri}`);
		await page.waitForLoadState("networkidle");
		if ((await page.locator("body[aria-busy]").count()) > 0) {
			await page
				.locator("body[aria-busy]")
				.waitFor({ state: "detached", timeout: 5000 })
				.catch(() => {});
		}
		if (uri.endsWith("/")) {
			uri += "index.html";
		}
		await page.screenshot({ path: `../../_screenshot/${uri}.png`, fullPage: true });
	}
});
