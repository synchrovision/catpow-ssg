export const scrollsync=function(el,param={}){
	const app={};
	const items=el.children;
	const updateActiveItem=()=>{
		let i,index=-1;
		const l=el.children.length;
		const bnd=el.getBoundingClientRect();
		const cx=bnd.left+bnd.width/2,cy=bnd.top+bnd.height/2;
		for(i=0;i<l;i++){
			const bnd=el.children[i].getBoundingClientRect();
			if(bnd.left<cx && bnd.top<cy && bnd.right>cx && bnd.bottom>cy){
				index=i;
				break;
			}
		}
		app.current=index;
		el.style.setProperty('--scroll-index',index);
	};
	app.goto=(index)=>{
		updateActiveItem();
	};
	app.prev=()=>app.goto(app.current-1);
	app.next=()=>app.goto(app.current+1);
	const registerAsNav=(items)=>{
		const l=items.length;
		for(let i=0;i<l;i++){
			items[i].addEventListener('click',()=>app.goto(i));
		}
	}
	if(param.nav){
		if(Array.isArray(param.nav)){
			param.nav.forEach((target)=>{registerAsNav(target.children);});
		}
		else{
			registerAsNav(param.nav.children);
		}
	}
	app.timer=setInterval(updateActiveItem,100);
	updateActiveItem();
	return app;
}