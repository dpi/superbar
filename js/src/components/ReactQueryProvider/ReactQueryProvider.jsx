import { QueryClient, QueryClientProvider } from "@tanstack/react-query"

/**
 * Set cache/stale time for the app.
 *
 * @type {number}
 *   Milliseconds * seconds * minutes.
 */
const cacheTime = 1000 * 60 * 5

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      cacheTime,
      staleTime: cacheTime,
      refetchOnMount: "always",
    },
  },
})

const ReactQueryProvider = ({ children }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
)

export { ReactQueryProvider, cacheTime, queryClient }
