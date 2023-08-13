export const parallax=(el,vars={})=>{
	var wh,ch;
	const updateCoef=()=>{
		wh=window.innerHeight,ch=el.clientHeight;
	};
	window.addEventListener('resize',updateCoef);
	updateCoef();
	const keys=Object.keys(vars);
	keys.forEach((key)=>{
		if(Array.isArray(vars[key])){
			const vs=vars[key];
			if(vs.length & 1){vs.push(1);}
			if(vs.length===2){
				const s=vs[0],c=1/(vs[1]-vs[0]);
				vars[key]=(p)=>Math.max(0,Math.min((p-s)*c,1));
			}
			else{
				const ss=[],es=[],cs=[];
				for(let i=0;i<vs.length;i+=2){
					ss.push(vs[i]);
					es.push(vs[i+1]);
					cs.push(1/(vs[i+1]-vs[i]));
				}
				vars[key]=(p)=>{
					for(let i=0;i<ss.length;i++){
						if(p<es[i]){
							const a=(p-ss[i])*cs[i];
							return Math.max(0,Math.min((i&1)?(1-a):a,1));
						}
					}
					return (ss.length&1)?1:0;
				}
			}
		}
	});
	
	const tick=(t)=>{
		const bnd=el.getBoundingClientRect();
		const p=Math.min(1,Math.max(0,(wh-bnd.top)/(bnd.height+wh)));
		el.style.setProperty('--parallax-t',bnd.top);
		el.style.setProperty('--parallax-c',bnd.top+ch/2-wh/2);
		el.style.setProperty('--parallax-b',wh-bnd.bottom);
		el.style.setProperty('--parallax-p',p);
		keys.map((key)=>el.style.setProperty('--'+key,vars[key](p)));
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
		body.style.setProperty('transform','translate3d(0,'+bnd.top+'px,0)');
		window.requestAnimationFrame(tick);
	}
	window.requestAnimationFrame(tick);
	body.style.setProperty('position','fixed');
	body.style.setProperty('overflow','hidden');
	body.style.setProperty('top',0);
	body.style.setProperty('left',0);
	body.style.setProperty('right',0);
	box.style.setProperty('height',body.getBoundingClientRect().height + 'px');
}