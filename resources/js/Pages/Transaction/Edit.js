import Input from "@/Components/Input";
import Label from "@/Components/Label";
import SidePanel from '@/Components/SidePanel';

export default function Edit({transaction, onClose}) {
    return (
        <SidePanel toggleOpen={! transaction ? false : true} 
                    onClose={onClose} 
                    title={"Edit Transaction"}>
            {
                transaction &&
                <div>
                    <div>
                        <Label forInput="amount" value="Amount" />

                        <Input
                            type="text"
                            name="amount"
                            value={transaction.amount}
                            className="mt-1 block w-full"
                        />
                    </div>

                    <div className="flex items-center justify-end mt-4">
                        <button onClick={onClose} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                            Save
                        </button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }