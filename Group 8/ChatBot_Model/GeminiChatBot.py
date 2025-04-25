import os
import google.generativeai as genai
import time
from google.api_core.exceptions import ResourceExhausted

max_retries = 5
retries = 0
api_key = os.environ.get("GEMINI_API_KEY")
if not api_key:
    raise ValueError("API Key not found. Please set the GEMINI_API_KEY environment variable.")

genai.configure(api_key=api_key)

generation_config = {
    "temperature": 0,
    "top_p": 0.95,
    "top_k": 64,
    "max_output_tokens": 8192,
    "response_mime_type": "text/plain",
}

model = genai.GenerativeModel(
    model_name="gemini-2.0-pro-exp-02-05",
    generation_config=generation_config,
    system_instruction=(
        "Act as a professional computer consultant with expertise in both hardware and software. "
        "Provide accurate and up-to-date recommendations based on the latest technologies and best practices. "
        "Keep responses concise and answer only the question asked. "
        "Avoid unnecessary introductions or explanations unless explicitly requested by the user. "
        "If clarification is needed, ask a short follow-up question. "
        "When starting a conversation, greet the user and ask how you can assist them."
        "Anything not related to Computers respond with I cant answer that!"
    )
)

history = []

print("Ask about Anything!")

while True:
    user_input = input("You: ")
    if user_input.lower() in ["exit", "quit", "bye"]:
        print("Bot: Goodbye! Have a great day!")
        break

    chat_session = model.start_chat(history=history)

    try:
        response = chat_session.send_message(user_input)
        model_response = response.text
        print(f'Bot: {model_response}\n')

        history.append({"role": "user", "parts": [user_input]})
        history.append({"role": "model", "parts": [model_response]})
    except ResourceExhausted as e:
        retries += 1
        print(f"Rate limit exceeded. Retrying ({retries}/{max_retries})...")
        time.sleep(5)
        if retries == max_retries:
            print("Max retries reached. Please try again later.")
            break
