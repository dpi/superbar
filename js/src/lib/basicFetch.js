export const basicFetch = async ({ queryKey }) => {
  const [, { url }] = queryKey
  if (!url) {
    return null
  }
  const response = await fetch(url)
  if (!response.ok) {
    throw new Error(`Network response was: ${response.status}`)
  }
  return await response.json()
}
