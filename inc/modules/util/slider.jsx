export const slider=function(el,param={}){
	const app=this;
	const items=el.children;
	app.param=Object.assign({interval:5000,autoPlay:true},param);
	const l=app.length=el.children.length;
	const h=l>>1;
	app.current=param.initialSlide || 0;
	app.goto=(i)=>{
		i=((i%l)+l)%l;
		app.current=i;
		for(let p=-h;p<l-h;p++){
			const item=el.children[(p+i+l)%l];
			item.classList.toggle('is-before',p<0);
			item.classList.toggle('is-prev',p===-1);
			item.classList.toggle('is-current',p===0);
			item.classList.toggle('is-next',p===1);
			item.classList.toggle('is-after',p>0);
		}
	}
	app.prev=()=>app.goto(app.current-1);
	app.next=()=>app.goto(app.current+1);
	app.stop=()=>{el.classList.remove('is-playing');if(app.timer){clearInterval(app.timer);}}
	app.play=()=>{app.stop();el.classList.add('is-playing');app.timer=setInterval(()=>app.next(),app.param.interval);}
	if(app.param.autoPlay){
		app.play();
		app.observer=new IntersectionObserver((entries)=>{
			entries.forEach((entry)=>{
				if(entry.intersectionRatio>0){app.play();}
				else{app.stop();}
			});
		});
		app.observer.observe(el);
	}
	app.goto(0);
	el.classList.add('is-init');
	window.requestAnimationFrame(()=>el.classList.remove('is-init'));
	return app;
}