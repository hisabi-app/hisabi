import Button from "@/Components/Button";
import Input from "@/Components/Input";
import Label from "@/Components/Label";

export default function Edit() {
    return (
        <form>
            <div>
                <Label forInput="amount" value="Amount" />

                <Input
                    type="text"
                    name="amount"
                    // value={data.email}
                    className="mt-1 block w-full"
                    // handleChange={onHandleChange}
                />
            </div>

            <div className="flex items-center justify-end mt-4">
                <Button className="ml-4 bg-blue-500">
                    Save
                </Button>
            </div>
        </form>
    )
  }