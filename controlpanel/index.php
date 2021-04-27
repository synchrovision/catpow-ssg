<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>ControlPanel</title>
<link rel="stylesheet" href="css/style.css">
<script type="module" src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
<script nomodule src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine-ie11.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.0.0/dist/alpine-ie11.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
	function app(){
		var con=axios.create({
			baseURL:'<?=API_URL?>'
		});
		return {
			baseURL:'<?=BASE_URL?>',
			currentPage:'',
			pages:[],
			updateIndex(){
				con.get('index').then((res)=>{
					this.currentPage=res.data[0];
					this.pages=res.data;
				});
			}
		};
	}
</script>
</head>

<body>
	<div class="siteLayout">
		<div class="siteHeader">
			<h2 class="siteHeader__title">Control Panel</h2>
		</div>
		<div class="siteMain" x-data="app()" x-init="updateIndex">
			<div class="siteMain__sidebar">
				<ul class="index">
					<template x-for="page in pages">
						<li class="index__item" :class="{active:page==currentPage}">
							<span x-text="page" @click="currentPage=page"></span>
							<a :href="page" target="_blank">»</a>
						</li>
					</template>
				</ul>
			</div>
			<div class="siteMain__contents">
				<div class="previews">
					<div class="preview preview_pc"><iframe class="preview__contents" :src="currentPage" frameborder="0"></iframe></div>
					<div class="preview preview_sp"><iframe class="preview__contents" :src="currentPage" frameborder="0"></iframe></div>
				</div>
			</div>
		</div>
		<div class="siteFooter">
			<p class="siteFooter__text">Catpow-SSG</o>
		</div>
	</div>
</body>
</html>
