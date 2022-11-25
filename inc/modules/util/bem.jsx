export const bem=(className)=>{
	const children={};
	return new Proxy((flags)=>{
		if(undefined!==flags){
			if(typeof flags === 'string'){return className+' '+flags;}
			const classes=Array.isArray(flags)?flags:Object.keys(flags).filter((c)=>flags[c]);
			if(classes.length>0){return className+' '+classes.join(' ');}
		}
		return className;
	},{
		get:(target,prop)=>{
			if(undefined===children[prop]){
				children[prop]=bem(className.split(' ')[0]+(prop[0]==='_'?'_':'-')+prop);
			}
			return children[prop];
		}
	});
};