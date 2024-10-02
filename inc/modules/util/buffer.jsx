export const debounce=(callback,interval)=>{
	let timer;
	return (e)=>{
		if(timer){clearTimeout(timer);}
		timer=setTimeout(callback,interval,e);
	};
};
export const throttle=(callback,interval)=>{
	let timer,hold=false;
	return (e)=>{
		if(hold){
			timer=setTimeout(callback,interval,e);
		}
		else{
			if(timer){clearTimeout(timer);}
			hold=true;
			callback(e);
			setTimeout(()=>hold=false,interval);
		}
	};
};