import os
import azure.cognitiveservices.speech as speechsdk

try:
    config = speechsdk.SpeechConfig(subscription=os.getenv("AZURE_SPEECH_KEY", "your_azure_speech_key_here"), region="southeastasia")
    print("SpeechConfig accepted by local SDK")
except Exception as e:
    print("Error with speech config:", e)

