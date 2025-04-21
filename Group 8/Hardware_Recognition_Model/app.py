import os
import google.generativeai as genai
from dotenv import load_dotenv
import mimetypes

load_dotenv()
api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    raise ValueError("API Key not found! Ensure you have a .env file with GEMINI_API_KEY set.")

genai.configure(api_key=api_key)
from dotenv import load_dotenv
import mimetypes


load_dotenv()
api_key = os.getenv("GEMINI_API_KEY")

if not api_key:
    raise ValueError("API Key not found! Ensure you have a .env file with GEMINI_API_KEY set.")

genai.configure(api_key=api_key)
def save_output_to_file(content):
    output_file_path = "output.txt"
    
    with open(output_file_path, "w", encoding="utf-8") as output_file:
        output_file.write(content)
    
    print(f"\nGenerated Description saved to '{output_file_path}':")
    print(content)

def upload_to_gemini(path):

    if not os.path.exists(path):
        raise FileNotFoundError(f"File '{path}' not found.")

    mime_type, _ = mimetypes.guess_type(path)
    if mime_type is None:
        raise ValueError("Could not determine MIME type. Please specify a valid image file.")

    file = genai.upload_file(path, mime_type=mime_type)
    print(f"Uploaded file '{file.display_name}' as: {file.uri}")
    return file


file_path = r"C:\Users\User\Pictures\Screenshots\Screenshot 2025-02-14 221948.png"  

try:
    file = upload_to_gemini(file_path)

    generation_config = {
        "temperature": 1,
        "top_p": 0.95,
        "top_k": 40,
        "max_output_tokens": 8192,
        "response_mime_type": "text/plain",
    }

    model = genai.GenerativeModel(
        model_name="gemini-2.0-flash",
        generation_config=generation_config,
    )

    response = model.generate_content([
        "What object is this? Describe how it might be used",
        "Object: The input is a PC hardware Image (any hardware component related to computers)",
        "Description: You are a computer parts expert. Develop a model to accurately identify PC hardware components from images, including but not limited to processors, graphics cards, motherboards, memory modules,  storage devices, and PSUs . The model should focus on specific characteristics like brand logos, form factors, and component features. The output should only be the exact name of the device, do not give anything more than the exact name for example, 'intel core i9 11th Gen' for a processor or 'ASUS B560 Motherboard' for a motherboard or 'MSI 3070ti' for a GPU. Please note that assistance is strictly limited to identifying PC hardware components; if given any other images simply reply with   I cannot provide support for any other types of images or objects'",
        "Object: ",
        file,
        "​",
        "Description: ",
    ])


    generated_text = response.text.strip()
    save_output_to_file(generated_text)


except FileNotFoundError:
    print(f"Error: File '{file_path}' not found. Please check the path.")
except ValueError as ve:
    print(f"Error: {ve}")
except Exception as e:
    print(f"An error occurred: {e}")
