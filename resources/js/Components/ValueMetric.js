import Card from "./Card";
import LoadingView from "./LoadingView";

function ValueMetric({ name, data }) {
    if(! data) {
        return (
            <Card className="relative">
                <LoadingView  />
            </Card>
        )
    }

    return (
        
        <Card>
            <div className="px-6 py-4">
                <div className="flex mb-4">
                    <h3 className="mr-3 text-base text-gray-700 font-bold">{ name }</h3>
                </div>

                <p className="flex items-center text-4xl mb-4">
                    { data }
                </p>
            </div>
        </Card>
    );
}

export default ValueMetric;