export const parallax=(el)=>{
	var wh,ch;
	const updateCoef=()=>{
		wh=window.innerHeight,ch=el.clientHeight;
	};
	window.addEventListener('resize',updateCoef);
	updateCoef();
	const tick=(t)=>{
		el.style.setProperty('--parallax-t',el.getBoundingClientRect().top);
		el.style.setProperty('--parallax-c',el.getBoundingClientRect().top+ch/2-wh/2);
		el.style.setProperty('--parallax-b',wh-el.getBoundingClientRect().bottom);
		window.requestAnimationFrame(tick);
	}
	window.requestAnimationFrame(tick);
};
export const dimensionBox=(box)=>{
	const body=box.children[0];
	const observer=new ResizeObserver((entries)=>{
		box.style.setProperty('height',entries[0].contentRect.height + 'px');
	});
	observer.observe(body);
	const tick=(t)=>{
		const bnd=box.getBoundingClientRect(),wh=window.innerHeight;
		body.style.setProperty('perspective-origin','center '+(wh/2-bnd.top)+'px');
		body.style.setProperty('top',bnd.top+'px');
		window.requestAnimationFrame(tick);
	}
	window.requestAnimationFrame(tick);
	body.style.setProperty('position','fixed');
	body.style.setProperty('overflow','hidden');
	body.style.setProperty('left',0);
	body.style.setProperty('right',0);
	box.style.setProperty('height',body.getBoundingClientRect().height + 'px');
}