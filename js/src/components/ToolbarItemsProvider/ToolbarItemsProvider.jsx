import React, { createContext, useContext } from "react"
import PropTypes from "prop-types"
import { useQuery } from "@tanstack/react-query"
import { basicFetch } from "../../lib/basicFetch"

const ToolbarItemsContext = createContext({})

const useToolbarItems = () => useContext(ToolbarItemsContext)

const ToolbarItemsProvider = ({ apiUrl, path, children }) => {
  const { data, isLoading, error } = useQuery(
    ["toolbar-items", { url: `${apiUrl}?path=${path}` }],
    basicFetch
  )
  console.log(data)
  return (
    <ToolbarItemsContext.Provider value={{ items: data, isLoading, error }}>
      {children}
    </ToolbarItemsContext.Provider>
  )
}

ToolbarItemsProvider.propTypes = {
  apiUrl: PropTypes.string.isRequired,
  path: PropTypes.string.isRequired,
}

export { ToolbarItemsProvider, useToolbarItems }
