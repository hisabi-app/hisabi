import React, { useState, useEffect } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SidePanel from '@/components/Global/SidePanel';
import { updateSms, deleteResource } from "../../Api";
import { Button } from "@/components/ui/button";
import { LongPressButton } from '@/components/ui/long-press-button';

export default function Edit({sms, onClose, onUpdate, onDelete}) {
    const [loading, setLoading] = useState(false);
    const [body, setBody] = useState('')

    useEffect(() => {
        if(! sms) return;

        setBody(sms.body)
    }, [sms])

    const update = () => {
        if(loading) { return }

        setLoading(true);

        updateSms({ id: sms.id, body })
            .then(({data}) => {
                setLoading(false);
                onUpdate(data.updateSms)
            })
            .catch(console.error);
    }

    return (
        <SidePanel toggleOpen={! sms ? false : true}
                    onClose={onClose}
                    title={"Fix SMS Parsing"}>
            <div className="p-1 rounded border-l-2 border-orange-500 pl-2 bg-orange-50">
                In order to make sure parsing this SMS is correct, please add the correspoding SMS template in the config file <span className="bg-orange-100 rounded px-1">config/hisabi.php</span> under <span className="bg-orange-100 rounded px-1">sms_templates</span>. <br/><br/> Once you finish, you can try to parse the SMS again. To learn more, please visit the <a className="underline" target="__blank" href="/docs/1.0/sms-parser">documentation</a>
            </div>
            {
                sms &&
                <div className="mt-6">
                    <div>
                        <Label htmlFor="body">
                            Body
                        </Label>

                        <Input
                            type="text"
                            name="body"
                            value={body}
                            className="mt-1 block w-full"
                            onChange={(e) => setBody(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center justify-between mt-4">
                        <LongPressButton
                            onLongPress={() => {
                                deleteResource({id: sms.id, resource: 'Sms'})
                                    .then(() => {
                                        onDelete(sms)
                                        onClose()
                                    })
                                    .catch(console.error);
                            }}
                        >
                            Hold to Delete
                        </LongPressButton>
                        <Button onClick={update}>
                            <span>Parse again</span>
                        </Button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }
