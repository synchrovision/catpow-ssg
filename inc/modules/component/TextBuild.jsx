
export const TextBuild=(props)=>{
	const {useState,useCallback,useMemo,useEffect}=React;
	const {className="cp-textbuild",children,delay=5,interval=150}=props;
	
	const [progress,setProgress]=useState(0);
	
	const letters=useMemo(()=>{
		const letters=[];
		children.forEach((line)=>{
			if(typeof line === 'string'){
				Array.from(line).forEach((letter)=>letters.push(letter));
			}
			else{
				letters.push(line);
			}
		});
		return letters;
	},[children]);
	
	useEffect(()=>{
		let progress=-delay; 
		const timer=setInterval(()=>{
			progress++;
			setProgress(progress);
			if(progress>letters.length){
				clearInterval(timer);
			}
		},interval);
		return ()=>clearInterval(timer);
	},[letters,delay,interval]);
	
	return (
		<div className={className}>
			{letters.map((letter,index)=>(
				(typeof letter === 'string')?
				(<span className={className+'__letter is-' + (index<progress?'show':'hide')}>{letter}</span>):letter
			))}
		</div>
	);
}