import { useEffect, useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

function App() {
  const [count, setCount] = useState(0)
  const [token, setToken] = useState('')

  const [csrfToken, SetCsrfToken] = useState<string>('');

  
  const handleToGetToken = () => {

    const myHeaders = new Headers();
    myHeaders.append("Content-Type", "application/json");

    const raw = JSON.stringify({
      "access_key": "1238978"
    });

    const requestOptions = {
      method: "POST",
      headers: myHeaders,
      body: raw,
    };

    fetch("/api/get-token", requestOptions)
      .then((response) => response.text())
      .then((result) => {
        console.log(result);
        setToken(JSON.stringify(result))
      })
      .catch((error) => console.error(error));
  }

  useEffect(() => {
    handleToGetToken();

    const token = document.head.querySelector('meta[name="csrf-token"]');
    SetCsrfToken(token?.getAttribute('content') ?? 'no csrf-token');
  }, [])

  /** get - user_data - 
   *      - form-radn
   */

  return (
    <>
      <div className='react-box'>
        <div className='react-box-img'>
          <a href="https://vitejs.dev" target="_blank">
            <img src={viteLogo} className="logo-react" alt="Vite logo" />
          </a>
          <a href="https://react.dev" target="_blank">
            <img src={reactLogo} className="logo-react react" alt="React logo" />
          </a>
        </div>
        <h1 className='read-the-docs'>Vite + React</h1>
        <br />
        <div>
          <button className='btn-class' onClick={() => setCount((count) => count + 1)}>
            count is {count}
          </button>
         
        </div>
        <p className="read-the-docs">
          Access Token - {token}
        </p>
        <br />
        <p className="read-the-docs">
          CSRF Token - {csrfToken}
        </p>
      </div>
    </>
  )
}

export default App
