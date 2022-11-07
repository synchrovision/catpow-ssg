export const fib=(n)=>{
	if(undefined!==fib.cache[n]){return fib.cache[n];}
	return fib.cache[n-2]+fib.cache[n-1];
};
fib.cache=[0,1,1];