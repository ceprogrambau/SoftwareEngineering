from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

def fetch_completed_courses_bau(student_id, password):
    options = Options()
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--window-size=1920x1080")
    options.add_argument("--disable-extensions")

    driver = webdriver.Chrome(options=options)

    try:
        driver.get("https://mis.bau.edu.lb/web/v12/iconnectv12/cas/sso.aspx")
        wait = WebDriverWait(driver, 20)

        wait.until(EC.presence_of_element_located((By.ID, "usernameUserInput")))
        wait.until(EC.presence_of_element_located((By.ID, "password")))

        driver.find_element(By.ID, "usernameUserInput").send_keys(student_id)
        driver.find_element(By.ID, "password").send_keys(password)

        driver.find_element(By.CLASS_NAME, "btnLogin").click()
        time.sleep(3)

        for _ in range(5):
            current_url = driver.current_url
            try:
                click_here = driver.find_element(By.LINK_TEXT, "Click here")
                click_here.click()
                time.sleep(3)
            except:
                break

        for _ in range(3):
            try:
                popup = driver.find_element(By.XPATH, "//button[contains(text(), 'I understand')]")
                popup.click()
                time.sleep(2)
            except:
                break

        if "portalhome.aspx" in driver.current_url:
            time.sleep(3)

            try:
                menu_button = driver.find_element(By.CLASS_NAME, "navbar-toggler")
                if menu_button.is_displayed():
                    menu_button.click()
                    time.sleep(2)
            except:
                pass

            max_retries = 5
            for attempt in range(max_retries):
                try:
                    xfiles_btn = driver.find_element(By.PARTIAL_LINK_TEXT, "X-Files")
                    xfiles_btn.click()
                    time.sleep(4)
                    if "profileV2.aspx" in driver.current_url:
                        break
                    elif "portalhome.aspx" in driver.current_url:
                        continue
                    else:
                        continue
                except Exception as e:
                    time.sleep(2)

        total_credits = ""
        try:
            total_credits_element = driver.find_element(By.ID, "lblEarnedCredits")
            total_credits = total_credits_element.text.strip() if total_credits_element else "0"
        except Exception as e:
            pass

        completed_courses = []
        sgpa = ""

        try:
            wait.until(EC.presence_of_element_located((By.ID, "AcademicHistory")))
            while True:
                table = driver.find_element(By.ID, "AcademicHistory")
                rows = table.find_elements(By.TAG_NAME, "tr")

                for i, row in enumerate(rows[1:]):
                    cols = row.find_elements(By.TAG_NAME, "td")
                    if len(cols) >= 2:
                        course = cols[1].text.strip()
                        if course and course not in completed_courses:
                            completed_courses.append(course)
                        if i == 0 and not sgpa:
                            sgpa = cols[6].text.strip()

                try:
                    next_btn = driver.find_element(By.ID, "AcademicHistory_next")
                    if "disabled" in next_btn.get_attribute("class"):
                        break
                    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", next_btn)
                    time.sleep(1)
                    next_btn.click()
                    time.sleep(2)
                except Exception as e:
                    break
        except Exception as e:
            pass

        return {
            "status": "success",
            "message": "Login and X-Files access confirmed",
            "completed_courses": completed_courses,
            "sgpa": sgpa,
            "total_credits": total_credits
        }

    except Exception as e:
        return {"status": "error", "message": f"Selenium error: {e}"}
    finally:
        driver.quit()
