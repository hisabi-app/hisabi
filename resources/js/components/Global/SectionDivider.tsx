export default function SectionDivider({title}: {title: string}) {
    return ( 
        <div className="flex items-center w-full py-3">
            <div className="flex-grow border-t border-gray-300"></div>
            <span className="px-4 text-lg text-gray-600 font-medium">{title}</span>
            <div className="flex-grow border-t border-gray-300"></div>
        </div>
     );
}