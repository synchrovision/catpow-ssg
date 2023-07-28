<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>ControlPanel</title>
<link rel="stylesheet" href="css/style.css">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<script>
	function app(){
		var con=axios.create({
			baseURL:'<?=API_URL?>'
		});
		return {
			baseURL:'<?=BASE_URL?>',
			currentPage:'',
			keyword:'',
			pages:[],
			init(){
				const {pc,lt,tb,sp}=this.$refs;
				const coefMap=new Map();
				const updateCoef=(iframe)=>{
					if(!pc.contentWindow.document){return;}
					const pcd=pc.contentWindow.document.documentElement;
					const pcsy=Math.max(1,pcd.scrollHeight-pcd.clientHeight);
					[lt,tb,sp].forEach((f)=>{
						if(!f.contentWindow.document){coefMap.set(f,1);return;}
						const d=f.contentWindow.document.documentElement;
						const sy=Math.max(1,d.scrollHeight-d.clientHeight);
						coefMap.set(f,sy/pcsy);
					});
				}
				const syncScroll=()=>{
					[lt,tb,sp].forEach((f)=>{
						f.contentWindow.scroll({
							top:coefMap.get(f)*pc.contentWindow.scrollY
						});
					});
				}
				pc.addEventListener('load',()=>{
					updateCoef();
					pc.contentWindow.addEventListener('scroll',syncScroll);
				});
				const timer=setInterval(updateCoef,100);
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
				<div class="search">
					<input type="text" class="search__input" x-model="keyword"/>
				</div>
				<ul class="index">
					<template x-for="page in pages">
						<li class="index__item" :class="{active:page==currentPage,visible:!keyword || page.includes(keyword)}">
							<span x-text="page" @click="currentPage=page"></span>
							<a class="icon" :href="page" target="_blank">open_in_new</a>
						</li>
					</template>
				</ul>
			</div>
			<div class="siteMain__contents">
				<div class="previews">
					<?php foreach(['pc','lt','tb','sp'] as $d): ?>
					<div class="preview preview_<?=$d?>">
						<iframe class="preview__contents" :src="currentPage" frameborder="0" x-ref="<?=$d?>"></iframe>
						<div class="preview__control">
							<div class="icon" @click="()=>$refs.<?=$d?>.contentWindow.location.reload()">replay</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="siteFooter">
			<p class="siteFooter__text">Catpow-SSG</o>
		</div>
	</div>
</body>
</html>
