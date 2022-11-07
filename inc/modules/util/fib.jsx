export const fib=(n)=>{
	if(undefined!==fib.cache[n]){return fib.cache[n];}
	return fib.cache[n]=fib(n-2)+fib(n-1);
};
fib.cache=[0,1];