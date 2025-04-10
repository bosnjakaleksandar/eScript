class ApiService {
  constructor() {
    this.baseUrl = "http://localhost:8002/api";
  }

  async get(endpoint) {
    const response = await fetch(this.baseUrl + endpoint, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      credentials: "include",
    });
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    return await response.json();
  }

  async post(endpoint, data) {
    const response = await fetch(this.baseUrl + endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(data),
      credentials: "include",
    });
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    return await response.json();
  }
}

export default new ApiService();
