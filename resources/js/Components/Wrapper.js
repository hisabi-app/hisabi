export default function Wrapper({ children, width='1/3' }) {
  return ( 
    <div className={`px-3 mb-6 w-1/2 w-full md:w-${width}`}>
      {children}
    </div> 
  );
}