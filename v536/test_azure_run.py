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
    if "AZURE_SPEECH_KEY" not in os.environ:
        os.environ["AZURE_SPEECH_KEY"] = "your_azure_speech_key_here"
    if "AZURE_SPEECH_REGION" not in os.environ:
        os.environ["AZURE_SPEECH_REGION"] = "southeastasia"
    asyncio.run(run())
