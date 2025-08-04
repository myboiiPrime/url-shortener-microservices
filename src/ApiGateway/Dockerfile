FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS base
WORKDIR /app
EXPOSE 80
EXPOSE 443

FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src
COPY ["src/ApiGateway/UrlShortener.ApiGateway.csproj", "src/ApiGateway/"]
COPY ["src/Shared/UrlShortener.Shared.csproj", "src/Shared/"]
RUN dotnet restore "src/ApiGateway/UrlShortener.ApiGateway.csproj"
COPY . .
WORKDIR "/src/src/ApiGateway"
RUN dotnet build "UrlShortener.ApiGateway.csproj" -c Release -o /app/build

FROM build AS publish
RUN dotnet publish "UrlShortener.ApiGateway.csproj" -c Release -o /app/publish /p:UseAppHost=false

FROM base AS final
WORKDIR /app
COPY --from=publish /app/publish .
ENTRYPOINT ["dotnet", "UrlShortener.ApiGateway.dll"]