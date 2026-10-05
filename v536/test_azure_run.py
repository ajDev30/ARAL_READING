import asyncio
from service import AzureSession
import os
import uuid

async def run():
    ws_queue = asyncio.Queue()
    session = AzureSession(
        loop=asyncio.get_running_loop(),
        ws_queue=ws_queue,
        locale="en-US",
        reference_text="hidden-passage"
    )
    print("starting...")
    await asyncio.to_thread(session.start)
    print("started!")
    
    await asyncio.to_thread(session.stop)

if __name__ == "__main__":
    os.environ["AZURE_SPEECH_KEY"] = "Dhb22yFffF4WaWXIWXayLQ9YLkVtllERZiga1G0QHMTb8yJGWrzmJQQJ99CIACqBBLyXJ3w3AAAYACOGIA3D"
    os.environ["AZURE_SPEECH_REGION"] = "southeastasia"
    asyncio.run(run())
