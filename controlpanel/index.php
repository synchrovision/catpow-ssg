<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>ControlPanel</title>
<link rel="stylesheet" href="css/style.css">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
			init(){
				const {pc,lt,tb,sp}=this.$refs;
				const syncScroll=()=>{
					const y=pc.contentWindow.scrollY,h=pc.contentWindow.innerHeight;
					const c=y/h;
					console.log({h,c});
					[lt,tb,sp].forEach((iframe)=>{
						iframe.contentWindow.scroll({
							top:c*iframe.contentWindow.innerHeight
						});
					});
				}
				pc.addEventListener('load',()=>{
					pc.contentWindow.addEventListener('scroll',syncScroll);
				});
			},
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
					<div class="preview preview_pc">
						<iframe class="preview__contents" :src="currentPage" frameborder="0" x-ref="pc"></iframe>
					</div>
					<div class="preview preview_lt">
						<iframe class="preview__contents" :src="currentPage" frameborder="0" x-ref="lt"></iframe>
					</div>
					<div class="preview preview_tb">
						<iframe class="preview__contents" :src="currentPage" frameborder="0" x-ref="tb"></iframe>
					</div>
					<div class="preview preview_sp">
						<iframe class="preview__contents" :src="currentPage" frameborder="0" x-ref="sp"></iframe>
					</div>
				</div>
			</div>
		</div>
		<div class="siteFooter">
			<p class="siteFooter__text">Catpow-SSG</o>
		</div>
	</div>
</body>
</html>
