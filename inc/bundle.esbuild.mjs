import * as esbuild from "esbuild";
import svgr from "esbuild-plugin-svgr";
import inlineImportPlugin from "esbuild-plugin-inline-import";
import * as sass from "sass";

import { parseArgs } from "node:util";

const { values, positionals } = parseArgs({
	options: {
		debugMode: {
			type: "boolean",
			default: false,
		},
		useGlobalReact: {
			type: "boolean",
			default: false,
		},
	},
	allowPositionals: true,
});

const { useGlobalReact, debugMode } = values;

let pathResolver = {
	name: "pathResolver",
	setup(build) {
		const externalModules = new Set(build.initialOptions.external || []);

		build.onResolve({ filter: /^catpow/ }, async (args) => {
			const result = await build.resolve("./" + args.path.slice(6), {
				kind: "import-statement",
				resolveDir: "./node_modules-included/catpow/src",
			});
			if (result.errors.length === 0) {
				return { path: result.path };
			}
		});
		build.onResolve({ filter: /^mjs:/ }, (args) => ({ path: args.path.slice(4), external: true }));
		build.onResolve({ filter: /^@?\w/ }, async (args) => {
			if (useGlobalReact && (args.path === "react" || args.path === "react-dom" || args.path === "react-dom/client")) {
				return {
					path: args.path,
					namespace: "react-global",
				};
			}
			if (externalModules.has(args.path)) {
				return { path: args.path, external: true };
			}
			const result = await build.resolve("./" + args.path, {
				kind: "import-statement",
				resolveDir: "./node_modules",
			});
			if (result.errors.length === 0) {
				return { path: result.path };
			}
		});
		if (useGlobalReact) {
			build.onLoad({ filter: /.*/, namespace: "react-global" }, async (args) => {
				if (args.path === "react-dom" || args.path === "react-dom/client") {
					return {
						contents: "export default window.ReactDOM;\n" + ["createRoot", "createPortal", "flushSync", "hydrateRoot"].map((h) => `export const ${h}=window.ReactDOM.${h};`).join("\n"),
						loader: "js",
					};
				}
				if (args.path === "react") {
					return {
						contents:
							"export default window.React;\n" +
							"export const version='18.0.0';\n" +
							[
								"useState",
								"useEffect",
								"useLayoutEffect",
								"useRef",
								"forwardRef",
								"useMemo",
								"useCallback",
								"createContext",
								"useContext",
								"useReducer",
								"createElement",
								"cloneElement",
								"isValidElement",
								"Fragment",
							]
								.map((h) => `export const ${h}=window.React.${h};`)
								.join("\n"),
						loader: "js",
					};
				}
			});
		}
	},
};
let inlineCssImporter = inlineImportPlugin({
	filter: /^css:/,
	transform: async (contents, args) => {
		return contents;
	},
});
let scssImporter = inlineImportPlugin({
	filter: /^scss:/,
	transform: async (contents, args) => {
		let { css } = sass.compileString(contents, {
			loadPaths: ["./", "./node_modules-included/", "../../_config/", "../../_tmpl/"],
		});
		return css;
	},
});

const setttings = {
	entryPoints: [positionals[0]],
	outfile: positionals[1],
	format: positionals[1].slice(-4) === ".mjs" ? "esm" : "iife",
	bundle: true,
	treeShaking: true,
	minify: !debugMode,
	plugins: [inlineCssImporter, scssImporter, pathResolver, svgr()],
};
if (useGlobalReact) {
	Object.assign(setttings, {
		define: {
			React: "window.React",
			"React.createElement": "window.React.createElement",
			"React.Fragment": "window.React.Fragment",
			ReactDOM: "window.ReactDOM",
			"ReactDOM.render": "window.ReactDOM.render",
		},
		external: ["react", "react-dom", "react-dom/client"],
	});
} else {
	Object.assign(setttings, {
		jsx: "automatic",
		jsxImportSource: "react",
	});
}
esbuild.build(setttings).catch((e) => console.log(e));
